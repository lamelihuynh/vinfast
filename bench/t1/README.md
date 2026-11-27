# `bench/t1/` — Thực Nghiệm & Công Cụ Dữ Liệu Topic T1 (Data Storage)

Thư mục này đóng gói độc lập toàn bộ công cụ vận hành liên CSDL, bộ benchmark đối chiếu **PostgreSQL 16 (Chuẩn hóa) vs MongoDB 7 (Document / Subset Pattern)** và kết quả báo cáo của **Topic T1**.

## Cấu trúc thư mục

```text
bench/t1/
├── README.md                  # Hướng dẫn sử dụng & cấu trúc thư mục
├── core/                      # Hạ tầng đo & nạp dữ liệu chuẩn hóa sang PG
│   ├── lib.php                # Hàm bấm giờ hrtime, p50/p95, ghi CSV, chốt an toàn DB
│   ├── schema_bench.sql       # DDL 5 bảng SQL chuẩn hóa (schema bench.*)
│   └── load_bench.php         # Đồng bộ catalog từ Mongo -> PG bench.* bằng COPY
├── experiments/               # 3 Thí nghiệm định lượng (chạy trên DB vinfast_bench)
│   ├── detail_read.php        # Thí nghiệm A (T1-19): findOne vs JOIN 4 bảng vs json_agg
│   ├── add_attribute.php      # Thí nghiệm B (T1-20): Chi phí thêm thuộc tính & khóa đọc
│   └── reviews.php            # Thí nghiệm C (T1-21): Review nhúng vs Bảng SQL vs Lai
├── tools/                     # Công cụ vận hành cho DB thật (vinfast)
│   ├── check_refs.php         # Đối soát tham chiếu chéo PG <-> Mongo 2 chiều (T1-23)
│   └── reviews_resync.php     # Đồng bộ lại avgRating, totalReviews & 5 review nhúng (T1-15)
├── report/                    # Tổng hợp bảng số liệu & vẽ biểu đồ
│   ├── summarize.php          # Gom results/raw/*.csv -> results/t1_summary_all.csv
│   └── plot.py                # Xuất 3 ảnh biểu đồ -> results/plots/*.png
└── results/                   # Toàn bộ kết quả thực nghiệm của riêng T1
    ├── t1_summary_all.csv     # Bảng tổng kết 48 dòng (p50, p95, mean, min, max)
    ├── raw/                   # 6 file CSV đo thô (30 runs/biến thể)
    ├── plans/                 # 2 file EXPLAIN (ANALYZE, BUFFERS) & executionStats
    └── plots/                 # 3 biểu đồ PNG chuẩn báo cáo
```

---

## 1. Công cụ vận hành cho Web thật (`vinfast`)

```powershell
# Kiểm tra tham chiếu chéo 2 chiều giữa MongoDB products và 5 bảng PostgreSQL
docker compose exec app php bench/t1/tools/check_refs.php

# Đồng bộ lại điểm trung bình & 5 review mới nhất từ PG sang MongoDB (toàn bộ hoặc 1 sản phẩm)
docker compose exec app php bench/t1/tools/reviews_resync.php
docker compose exec app php bench/t1/tools/reviews_resync.php --product=165
```

---

## 2. Chạy lại bộ Benchmark trên DB cô lập (`vinfast_bench`)

> **An toàn:** Mọi script trong `core/` và `experiments/` đều gọi `bench_require_bench_db()` tự động chặn nếu `PG_DB` hoặc `MONGO_DB` đang trỏ vào `vinfast`.

```powershell
# Bước 1: Tạo DB benchmark riêng (chỉ cần chạy 1 lần)
docker compose exec -T postgres createdb -U vinfast vinfast_bench
docker compose run --rm -e MONGO_DB=vinfast_bench mongo-init

# Bước 2: Seed dữ liệu (10k hoặc 100k) và nạp sang schema chuẩn hóa bench.*
docker compose run --rm -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench seed php database/seed/seed.php --force --size=10k
Get-Content bench/t1/core/schema_bench.sql | docker compose exec -T postgres psql -U vinfast -d vinfast_bench -v ON_ERROR_STOP=1
docker compose exec -T -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app php bench/t1/core/load_bench.php

# Bước 3: Chạy 3 thí nghiệm A, B, C
docker compose exec -T -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app php bench/t1/experiments/detail_read.php --size=10k --warmup=5 --runs=30
docker compose exec -T -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app php bench/t1/experiments/add_attribute.php --size=10k --warmup=5 --runs=30
docker compose exec -T -e PG_DB=vinfast_bench -e MONGO_DB=vinfast_bench app php bench/t1/experiments/reviews.php --size=10k --levels=10,1000,10000 --products=20 --warmup=5 --runs=30

# Bước 4: Tổng hợp bảng t1_summary_all.csv và xuất 3 biểu đồ PNG
docker compose exec -T app php bench/t1/report/summarize.php
python bench/t1/report/plot.py
```
