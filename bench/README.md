# bench/ — Benchmark các topic

| Thư mục | Topic | Nội dung dự kiến |
|---|---|---|
| `t1/` | Data storage | So sánh catalog SQL chuẩn hóa vs Mongo document; review embedded vs bảng riêng |
| `t2/` | Indexing | `EXPLAIN ANALYZE` / `explain('executionStats')` trước–sau index |
| `t3/` | Query processing | Dashboard SQL vs aggregation pipeline; join trong DB vs join ở backend |
| `t4/` | Transaction | [Demo checkout và smoke rollback](t4/README.md) |
| `t5/` | Concurrency | k6 tranh chấp tồn kho (pessimistic / optimistic / atomic / naive), isolation 2 session |
| `results/` | Tất cả | CSV kết quả: `<topic>_<thi-nghiem>_<size>.csv` |

## Dữ liệu
Dùng DB riêng cho benchmark để không đụng DB dev (xem `database/seed/README.md`):
```bash
docker compose exec -T postgres createdb -U vinfast vinfast_bench
docker compose run --rm -e MONGO_DB=vinfast_bench mongo-init
docker compose run --rm -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench seed php database/seed/seed.php --force --size=10k
```

## Chạy
```bash
# SQL
docker compose exec -T postgres psql -U vinfast -d vinfast_bench -f /dev/stdin < bench/t2/xxx.sql
# Mongo
docker compose exec -T mongo mongosh vinfast_bench --quiet --file /dev/stdin < bench/t2/xxx.js
# k6 (service riêng, profile bench)
docker compose -f docker-compose.yml -f docker-compose.bench.yml --profile bench run --rm k6 run /bench/t5/smoke.js
```
Muốn app dùng DB benchmark khi chạy k6: `PG_DB=vinfast_bench MONGO_DB=vinfast_bench` cho service `app`
(sửa tạm `docker-compose.bench.yml`), ghi rõ cấu hình máy và kích thước dữ liệu trong báo cáo.
