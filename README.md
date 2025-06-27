# MLM Reward Distribution System

A Laravel-based system for managing multi-level marketing (MLM) distributors, tracking their sales, and calculating monthly qualification and reward distribution.

## 🚀 Features

- **Distributor hierarchy**: Support for parent-child distributor relationships.
- **Monthly sales tracking**: Personal sales and recursive branch sales.
- **Qualification logic**:
  - Personal sales ≥ 5,000,000 VND for 3 consecutive months.
  - At least 2 branches with ≥ 250,000,000 VND in total branch sales for 3 consecutive months.
  - Retention and loss of qualification rules.
- **Monthly reward pool**:
  - Calculated as 1% of total system sales.
  - Evenly divided among qualified distributors.
- **Seeder**:
  - Generates sample data with 5 top-level distributors, some with qualified child branches.
- **Order system**:
  - Tracks actual orders per distributor.

## 📁 Key Models

- `Distributor`
- `DistributorMonthlyStats`
- `Order`
- `MonthlyReward`

If year/month are omitted, it uses the current month.

## 📊 Reward Calculation

- **reward_pool** = 1% * total sales in system
- **qualified_count** = number of NPPs who meet conditions
- **reward_per_distributor** = reward_pool / qualified_count

Saved in `monthly_rewards` table for tracking.

> Built with ❤️ by Pham Tuan.

