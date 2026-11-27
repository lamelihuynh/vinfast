<?php

/** app/core/Exceptions.php — Exception dùng chung cho OrderService / InventoryService (T4, T5) */
class OutOfStockException extends RuntimeException {}

class ConcurrentUpdateException extends RuntimeException {}

class CouponException extends RuntimeException {}
