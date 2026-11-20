// k6 smoke test: kiểm tra k6 gọi được app trong mạng Docker. T5 thay bằng kịch bản tranh chấp tồn kho.
import http from 'k6/http';
import { check } from 'k6';

export const options = { vus: 10, duration: '10s' };
const BASE = __ENV.BASE_URL || 'http://app';

export default function () {
  const res = http.get(`${BASE}/products/detail/190`);
  check(res, { 'status 200': (r) => r.status === 200 });
}
