// bench/t3/activity_logs.js — T3-15: explain the userActivityLogs pipelines and the $lookup stage-order experiment
//   docker compose exec -T mongo mongosh vinfast_bench --quiet --file /dev/stdin < bench/t3/activity_logs.js
// Same pipelines as ActivityLog::topKeywords / topViewedProducts.

const since = new Date(Date.now() - 30 * 864e5);
const logs = db.userActivityLogs;

// Pull the numbers that matter out of an aggregate explain (classic or SBE, sharded or not).
function summarize(label, exp) {
  const stages = exp.stages || [{ $cursor: exp }];
  const cursor = stages[0].$cursor || exp;
  const plan = JSON.stringify(cursor.queryPlanner.winningPlan);
  const es = cursor.executionStats || {};
  const lookup = stages.find((s) => s.$lookup);
  print(`-- ${label}`);
  print(`   first stage uses: ${plan.includes('IXSCAN') ? 'IXSCAN' : 'COLLSCAN'}` +
        `${(plan.match(/"indexName":"([^"]+)"/) || [])[1] ? ' on ' + plan.match(/"indexName":"([^"]+)"/)[1] : ''}`);
  print(`   keysExamined ${es.totalKeysExamined}, docsExamined ${es.totalDocsExamined}, nReturned ${es.nReturned}`);
  if (lookup) {
    print(`   $lookup: ran for ${lookup.nReturned} docs, products docs examined ${lookup.totalDocsExamined}, ` +
          `collection scans ${lookup.collectionScans}, indexesUsed ${JSON.stringify(lookup.indexesUsed)}`);
  }
  print(`   executionTimeMillis ${es.executionTimeMillis}` +
        `${lookup ? ', $lookup stage est. ' + lookup.executionTimeMillisEstimate + ' ms' : ''}`);
}

print(`search logs in 30 days: ${logs.countDocuments({ actionType: 'search', timestamp: { $gte: since } })}`);
print(`view_product logs in 30 days: ${logs.countDocuments({ actionType: 'view_product', timestamp: { $gte: since } })}`);

const keywords = [
  { $match: { actionType: 'search', timestamp: { $gte: since } } },
  { $project: { userId: 1, kw: { $toLower: { $trim: { input: { $ifNull: ['$metadata.keyword', ''] } } } } } },
  { $match: { kw: { $ne: '' } } },
  { $group: { _id: '$kw', count: { $sum: 1 }, users: { $addToSet: '$userId' } } },
  { $project: { _id: 0, keyword: '$_id', count: 1, uniqueUsers: { $size: { $setDifference: ['$users', [null]] } } } },
  { $sort: { count: -1, keyword: 1 } },
  { $limit: 10 },
];
print('\n== 1. Top keywords ==');
summarize('topKeywords', logs.explain('executionStats').aggregate(keywords));
printjson(logs.aggregate(keywords).toArray().slice(0, 5));

// Pitfall: a $project before the first $match. The optimizer may still push the $match
// down; this checks whether it did.
print('\n== 2. Pitfall: $addFields before $match ==');
summarize('addFields first', logs.explain('executionStats').aggregate([
  { $addFields: { kw: { $toLower: '$metadata.keyword' } } },
  { $match: { actionType: 'search', timestamp: { $gte: since } } },
  { $group: { _id: '$kw', count: { $sum: 1 } } },
]));

const lookup = { $lookup: { from: 'products', localField: '_id', foreignField: 'productID', as: 'p' } };
const viewedLimitFirst = [
  { $match: { actionType: 'view_product', timestamp: { $gte: since } } },
  { $group: { _id: '$targetId', views: { $sum: 1 } } },
  { $sort: { views: -1, _id: 1 } },
  { $limit: 10 },
  lookup,
  { $unwind: { path: '$p', preserveNullAndEmptyArrays: true } },
  { $project: { _id: 0, productID: '$_id', name: '$p.name', views: 1 } },
];
// Same result, but every distinct product is looked up before the top 10 are cut.
const viewedLookupFirst = [
  { $match: { actionType: 'view_product', timestamp: { $gte: since } } },
  { $group: { _id: '$targetId', views: { $sum: 1 } } },
  lookup,
  { $unwind: { path: '$p', preserveNullAndEmptyArrays: true } },
  { $sort: { views: -1, _id: 1 } },
  { $limit: 10 },
  { $project: { _id: 0, productID: '$_id', name: '$p.name', views: 1 } },
];

print('\n== 3. Top viewed: $limit before $lookup (app) vs $lookup before $sort/$limit ==');
summarize('limit -> lookup', logs.explain('executionStats').aggregate(viewedLimitFirst));
summarize('lookup -> limit', logs.explain('executionStats').aggregate(viewedLookupFirst));

function wallMs(pipeline, runs) {
  logs.aggregate(pipeline).toArray();
  const t = [];
  for (let i = 0; i < runs; i++) {
    const s = Date.now();
    logs.aggregate(pipeline).toArray();
    t.push(Date.now() - s);
  }
  t.sort((a, b) => a - b);
  return t[Math.floor(runs / 2)];
}
print(`   wall p50 over 20 runs: limit->lookup ${wallMs(viewedLimitFirst, 20)} ms, lookup->limit ${wallMs(viewedLookupFirst, 20)} ms`);
const a = JSON.stringify(logs.aggregate(viewedLimitFirst).toArray());
const b = JSON.stringify(logs.aggregate(viewedLookupFirst).toArray());
print(`   same result: ${a === b}`);
