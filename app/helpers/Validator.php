<?php
/**
 * app/helpers/Validator.php — Server-side Form Validation
 * Owner: All members (common)
 *
 * Usage:
 *   $v = new Validator($_POST);
 *   $v->required("email")->email("email")->minLen("password", 8);
 *   if ($v->fails()) { $_SESSION["errors"] = $v->errors(); ... }
 */
class Validator {
    private array $d, $e = [];
    public function __construct(array $data) { $this->d = $data; }

    public function required(string $f): self {
        if (empty(trim($this->d[$f] ?? ''))) $this->e[$f] = ucfirst($f) . ' is required.';
        return $this;
    }
    public function email(string $f): self {
        if (!empty($this->d[$f]) && !filter_var($this->d[$f], FILTER_VALIDATE_EMAIL))
            $this->e[$f] = 'Invalid email address.';
        return $this;
    }
    public function minLen(string $f, int $n): self {
        if (!empty($this->d[$f]) && mb_strlen($this->d[$f]) < $n)
            $this->e[$f] = ucfirst($f) . " must be at least {$n} characters.";
        return $this;
    }
    public function maxLen(string $f, int $n): self {
        if (!empty($this->d[$f]) && mb_strlen($this->d[$f]) > $n)
            $this->e[$f] = ucfirst($f) . " must not exceed {$n} characters.";
        return $this;
    }
    public function numeric(string $f): self {
        if (!empty($this->d[$f]) && !is_numeric($this->d[$f]))
            $this->e[$f] = ucfirst($f) . ' must be a number.';
        return $this;
    }
    public function phone(string $f): self {
        if (!empty($this->d[$f]) && !preg_match('/^0[0-9]{9}$/', (string)$this->d[$f]))
            $this->e[$f] = 'Số điện thoại phải bao gồm 10 chữ số và bắt đầu bằng số 0.';
        return $this;
    }
    public function fails(): bool   { return !empty($this->e); }
    public function errors(): array { return $this->e; }
}
