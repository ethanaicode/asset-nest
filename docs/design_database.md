核心思想是：

> **统一“钱的流动（Transaction） + 统一“对象（Item） + 分层扩展能力”**

## 🧠 一、整体架构

```bash
Item（你拥有什么 / 在付费什么）
 ├── Plan（怎么收费：订阅 / 一次性）
 ├── Asset（是否形成资产）
 └── Account（账号信息，可选）

Transaction（所有钱的流动） 核心
 └── PaymentMethod（钱从哪里来）

+ Category（分类）
+ Tag（标签）
```

## 🧱 二、完整数据库设计

## 1、items（统一入口）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  name VARCHAR(255) NOT NULL,
  cover_url VARCHAR(255),
  type ENUM('service', 'asset', 'account') NOT NULL,

  category_id BIGINT UNSIGNED NULL,

  notes TEXT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_items_type (type),
  KEY idx_items_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

👉 一切的起点（Netflix / MacBook / ChatGPT账号）

## 2、plans（计费方式）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_plans (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  item_id BIGINT UNSIGNED NOT NULL,

  type ENUM('one_time', 'recurring') NOT NULL,

  currency CHAR(3) NOT NULL DEFAULT 'CNY',

  -- 一次性
  one_time_price DECIMAL(10,2),
  purchase_date DATE,

  -- 订阅
  recurring_price DECIMAL(10,2),
  billing_cycle ENUM('monthly', 'yearly'),
  billing_day INT,
  start_date DATE,
  end_date DATE,

  default_payment_method_id BIGINT UNSIGNED,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_plans_item_id (item_id),
  KEY idx_plans_type (type),
  KEY idx_plans_default_payment_method_id (default_payment_method_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 3、transactions（资金流水核心）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  item_id BIGINT UNSIGNED,
  plan_id BIGINT UNSIGNED,

  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'CNY',

  type ENUM('expense', 'income', 'refund', 'transfer') NOT NULL,

  payment_method_id BIGINT UNSIGNED,

  transaction_date DATETIME NOT NULL,

  status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'success',

  notes TEXT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_transactions_item_id (item_id),
  KEY idx_transactions_plan_id (plan_id),
  KEY idx_transactions_payment_method_id (payment_method_id),
  KEY idx_transactions_transaction_date (transaction_date),
  KEY idx_transactions_type_status (type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

👉 **未来所有记账功能都靠它**

## 4、payment_methods（支付方式 / 信用卡）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_payment_methods (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  name VARCHAR(100) NOT NULL,
  type ENUM('credit_card', 'debit_card', 'cash', 'bank') NOT NULL,

  provider VARCHAR(100) COMMENT '服务组织',

  billing_day INT COMMENT '账单日',
  due_day INT COMMENT '还款日',
  credit_limit DECIMAL(12,2) COMMENT '信用额度',

  currency CHAR(3) NOT NULL DEFAULT 'CNY',

  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_payment_methods_type (type),
  KEY idx_payment_methods_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 5、assets（实物资产）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_assets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  item_id BIGINT UNSIGNED NOT NULL,

  quantity INT UNSIGNED NOT NULL DEFAULT 1,

  purchase_price DECIMAL(12,2),
  purchase_date DATE,

  current_value DECIMAL(12,2),

  status ENUM('active', 'sold', 'lost') NOT NULL DEFAULT 'active',

  location VARCHAR(255),

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_assets_item_id (item_id),
  KEY idx_assets_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 6、categories（分类）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  parent_id BIGINT UNSIGNED,

  PRIMARY KEY (id),
  UNIQUE KEY uk_categories_name (name),
  KEY idx_categories_parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

👉 支持层级（娱乐 / 工具 / 数码）

## 7、tags（标签系统，强烈推荐）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_tags (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uk_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fa_asset_item_tags (
  item_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,

  PRIMARY KEY (item_id, tag_id),
  KEY idx_item_tags_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 8、accounts（账号管理，扩展模块）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_accounts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  item_id BIGINT UNSIGNED,

  username VARCHAR(255),
  email VARCHAR(255),

  password_encrypted TEXT,

  notes TEXT,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_accounts_item_id (item_id),
  KEY idx_accounts_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

👉 未来你可以接：

- 自动登录
- 账号共享管理

## 9、credit_card_bills（信用卡账单，进阶）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_credit_card_bills (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  payment_method_id BIGINT UNSIGNED NOT NULL,

  period_start DATE,
  period_end DATE,

  due_date DATE,

  total_amount DECIMAL(12,2),

  is_paid TINYINT(1) NOT NULL DEFAULT 0,

  PRIMARY KEY (id),
  UNIQUE KEY uk_credit_card_bills_period (payment_method_id, period_start, period_end),
  KEY idx_credit_card_bills_due_date (due_date),
  KEY idx_credit_card_bills_is_paid (is_paid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 10、transfers（账户间转账，可选）

```sql
CREATE TABLE IF NOT EXISTS fa_asset_transfers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  from_payment_method_id BIGINT UNSIGNED NOT NULL,
  to_payment_method_id BIGINT UNSIGNED NOT NULL,

  amount DECIMAL(12,2) NOT NULL,

  transaction_date DATETIME NOT NULL,

  PRIMARY KEY (id),
  KEY idx_transfers_from_payment_method_id (from_payment_method_id),
  KEY idx_transfers_to_payment_method_id (to_payment_method_id),
  KEY idx_transfers_transaction_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 🔗 三、核心关系总结（非常重要）

```
Item（核心对象）
 ├── Plan（收费规则）
 ├── Asset（是否形成资产）
 ├── Account（账号信息）
 └── Transaction（所有钱的流动）

Transaction
 └── PaymentMethod（资金来源）
```

## 🚀 四、这套设计能支持什么（你未来会用到）

### ✔ 订阅管理

- 自动计算每月支出
- 提醒扣费

### ✔ 资产管理

- 当前总资产
- 折旧 / 盈亏

### ✔ 记账系统（重点）

你已经完成了 80%：

👉 所有消费就是 transactions

### ✔ 信用卡管理

- 每张卡花了多少
- 账单周期统计
- 还款提醒

### ✔ 账号管理

- 记录账号密码
- 绑定到订阅服务