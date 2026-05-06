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
CREATE TABLE items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(255) NOT NULL,
  type ENUM('service', 'asset', 'account') NOT NULL,

  category_id BIGINT,
  platform VARCHAR(100),

  notes TEXT,

  created_at DATETIME,
  updated_at DATETIME
);
```

👉 一切的起点（Netflix / MacBook / ChatGPT账号）

## 2、plans（计费方式）

```sql
CREATE TABLE plans (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  item_id BIGINT NOT NULL,

  type ENUM('one_time', 'recurring') NOT NULL,

  currency VARCHAR(10),

  -- 一次性
  one_time_price DECIMAL(10,2),
  purchase_date DATE,

  -- 订阅
  recurring_price DECIMAL(10,2),
  billing_cycle ENUM('monthly', 'yearly'),
  billing_day INT,
  start_date DATE,
  end_date DATE,

  default_payment_method_id BIGINT,

  created_at DATETIME,
  updated_at DATETIME
);
```

## 3、transactions（资金流水核心）

```sql
CREATE TABLE transactions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  item_id BIGINT,
  plan_id BIGINT,

  amount DECIMAL(12,2) NOT NULL,
  currency VARCHAR(10),

  type ENUM('expense', 'income', 'refund', 'transfer'),

  payment_method_id BIGINT,

  transaction_date DATETIME,

  status ENUM('pending', 'success', 'failed'),

  notes TEXT,

  created_at DATETIME
);
```

👉 **未来所有记账功能都靠它**

## 4、payment_methods（支付方式 / 信用卡）

```sql
CREATE TABLE payment_methods (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(100),
  type ENUM('credit_card', 'debit_card', 'paypal', 'cash', 'bank'),

  provider VARCHAR(100),
  last4 VARCHAR(10),

  billing_day INT,
  due_day INT,

  credit_limit DECIMAL(12,2),

  currency VARCHAR(10),

  is_active BOOLEAN DEFAULT TRUE,

  created_at DATETIME,
  updated_at DATETIME
);
```

## 5、assets（实物资产）

```sql
CREATE TABLE assets (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  item_id BIGINT NOT NULL,

  quantity INT DEFAULT 1,

  purchase_price DECIMAL(12,2),
  purchase_date DATE,

  current_value DECIMAL(12,2),

  status ENUM('active', 'sold', 'lost'),

  location VARCHAR(255),

  created_at DATETIME,
  updated_at DATETIME
);
```

## 6、categories（分类）

```sql
CREATE TABLE categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  parent_id BIGINT
);
```

👉 支持层级（娱乐 / 工具 / 数码）

## 7、tags（标签系统，强烈推荐）

```sql
CREATE TABLE tags (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50)
);

CREATE TABLE item_tags (
  item_id BIGINT,
  tag_id BIGINT
);
```

## 8、accounts（账号管理，扩展模块）

```sql
CREATE TABLE accounts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  item_id BIGINT,

  username VARCHAR(255),
  email VARCHAR(255),

  password_encrypted TEXT,

  notes TEXT,

  created_at DATETIME
);
```

👉 未来你可以接：

- 自动登录
- 账号共享管理

## 9、credit_card_bills（信用卡账单，进阶）

```sql
CREATE TABLE credit_card_bills (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  payment_method_id BIGINT,

  period_start DATE,
  period_end DATE,

  due_date DATE,

  total_amount DECIMAL(12,2),

  is_paid BOOLEAN DEFAULT FALSE
);
```

## 10、transfers（账户间转账，可选）

```sql
CREATE TABLE transfers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  from_payment_method_id BIGINT,
  to_payment_method_id BIGINT,

  amount DECIMAL(12,2),

  transaction_date DATETIME
);
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