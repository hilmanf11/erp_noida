-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 14 Okt 2026 pada 13.18
-- Versi server: 5.7.44-log
-- Versi PHP: 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `noida`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_balance_customers`
--

CREATE TABLE `account_balance_customers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) NOT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `currency_local` varchar(5) DEFAULT NULL,
  `balance` decimal(20,4) DEFAULT '0.0000',
  `balance_local` decimal(20,2) NOT NULL DEFAULT '0.00',
  `credit_limit` decimal(20,4) DEFAULT '0.0000',
  `start_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_balance_suppliers`
--

CREATE TABLE `account_balance_suppliers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) NOT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `currency_local` varchar(5) DEFAULT NULL,
  `balance` decimal(20,4) DEFAULT '0.0000',
  `balance_local` decimal(20,2) NOT NULL DEFAULT '0.00',
  `debt_limit` decimal(20,4) DEFAULT '0.0000',
  `start_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_banks`
--

CREATE TABLE `account_banks` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_detail_id` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_account` varchar(30) NOT NULL,
  `bank_code` varchar(30) NOT NULL,
  `ifsc_code` varchar(30) DEFAULT NULL,
  `currency` varchar(5) NOT NULL,
  `currency_local` varchar(10) DEFAULT NULL,
  `balance` decimal(20,2) DEFAULT '0.00',
  `balance_local` decimal(20,2) DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `posting_to` varchar(30) DEFAULT NULL,
  `p_supplier` tinyint(1) NOT NULL DEFAULT '0',
  `p_customer` tinyint(1) NOT NULL DEFAULT '0',
  `p_journal` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_cash_flow`
--

CREATE TABLE `account_cash_flow` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(10) DEFAULT NULL,
  `name_eng` varchar(100) NOT NULL,
  `name_idn` varchar(100) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `flag` tinyint(2) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_coa`
--

CREATE TABLE `account_coa` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_detail_id` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `cutoff` date NOT NULL DEFAULT '2024-10-31',
  `original_currency` varchar(10) DEFAULT NULL,
  `original_debit` decimal(20,2) DEFAULT '0.00',
  `original_kredit` decimal(20,2) DEFAULT '0.00',
  `local_currency` varchar(10) DEFAULT NULL,
  `local_debit` decimal(20,2) DEFAULT '0.00',
  `local_kredit` decimal(20,2) DEFAULT '0.00',
  `other` varchar(2) DEFAULT NULL COMMENT 'Untuk validasi di AP Payment atau AR Receipt ketika pilih type',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_groups`
--

CREATE TABLE `account_groups` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_group_details`
--

CREATE TABLE `account_group_details` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_id` varchar(30) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_imprests`
--

CREATE TABLE `account_imprests` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `currency_original` varchar(5) DEFAULT NULL,
  `currency_local` varchar(5) DEFAULT NULL,
  `amount_original` decimal(20,4) DEFAULT '0.0000',
  `amount_local` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_lock`
--

CREATE TABLE `account_lock` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `period` varchar(40) DEFAULT NULL,
  `menus_id` varchar(30) DEFAULT NULL,
  `lock` tinyint(1) NOT NULL DEFAULT '0',
  `remarks` text,
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_period`
--

CREATE TABLE `account_period` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `period` varchar(40) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_statements`
--

CREATE TABLE `account_statements` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `modul` varchar(30) DEFAULT 'Balance Sheet',
  `description` text,
  `account_number` varchar(30) NOT NULL,
  `flag` tinyint(2) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `approvals`
--

CREATE TABLE `approvals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `user_approval_1` varchar(30) DEFAULT NULL,
  `user_approval_2` varchar(30) DEFAULT NULL,
  `user_approval_3` varchar(30) DEFAULT NULL,
  `user_approval_4` varchar(30) DEFAULT NULL,
  `user_approval_5` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ap_payments`
--

CREATE TABLE `ap_payments` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `payment_type` varchar(30) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_no` varchar(30) DEFAULT NULL,
  `payment_by` varchar(30) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL,
  `cheque_no` varchar(30) DEFAULT NULL,
  `purchase_invoice` varchar(30) DEFAULT NULL,
  `supplier_invoice` varchar(30) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `payment` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_payment` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(20,4) NOT NULL DEFAULT '1.0000',
  `remarks` text,
  `note` text,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `other_account` varchar(30) DEFAULT NULL,
  `imprest_account` varchar(30) DEFAULT NULL,
  `status_dp` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `status_cl` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ap_payment_journals`
--

CREATE TABLE `ap_payment_journals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `payment_no` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `local_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `local_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `flag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ar_receipts`
--

CREATE TABLE `ar_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `receipt_type` varchar(30) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(30) DEFAULT NULL,
  `receipt_by` varchar(30) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL,
  `cheque_no` varchar(30) DEFAULT NULL,
  `sales_invoice` varchar(30) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `so_number` varchar(30) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `receipt` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_receipt` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(20,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `note` text,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `other_account` varchar(30) DEFAULT NULL,
  `status_dp` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `status_cl` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ar_receipt_journals`
--

CREATE TABLE `ar_receipt_journals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `receipt_no` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `local_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `local_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `flag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `assets`
--

CREATE TABLE `assets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `request_no` varchar(30) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `qty` int(11) NOT NULL DEFAULT '0',
  `uom` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` text,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `flag` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_disposals`
--

CREATE TABLE `asset_disposals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `asset_fixed_id` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `disposal_date` date DEFAULT NULL,
  `disposal` decimal(20,2) DEFAULT '0.00',
  `book_value` decimal(20,2) DEFAULT '0.00',
  `gainloss_type` varchar(10) DEFAULT NULL,
  `gainloss_value` decimal(20,2) DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_fixeds`
--

CREATE TABLE `asset_fixeds` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `purchase_invoice_no` varchar(30) NOT NULL,
  `voucher_no` varchar(30) DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `supplier_name` varchar(50) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `number` varchar(100) DEFAULT NULL,
  `numbers` int(11) NOT NULL DEFAULT '1',
  `name` varchar(50) DEFAULT NULL,
  `shift` tinyint(4) NOT NULL DEFAULT '0',
  `trans_date` date DEFAULT NULL,
  `usage_date` date DEFAULT NULL,
  `capitalization_date` date DEFAULT NULL,
  `uom` varchar(20) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `currency` varchar(10) DEFAULT NULL,
  `cost` decimal(20,2) DEFAULT '0.00',
  `estimate_day` int(11) NOT NULL DEFAULT '0',
  `estimate_month` int(11) DEFAULT '0',
  `estimate_year` tinyint(4) NOT NULL DEFAULT '0',
  `residual` int(11) NOT NULL DEFAULT '0',
  `expired_date` date DEFAULT NULL,
  `depreciation` decimal(20,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `method` varchar(30) DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT '0.00',
  `acq_period` decimal(20,2) NOT NULL DEFAULT '0.00',
  `acq_closing` decimal(20,2) NOT NULL DEFAULT '0.00',
  `addition_op` decimal(20,2) NOT NULL DEFAULT '0.00',
  `addition` decimal(20,2) NOT NULL DEFAULT '0.00',
  `addition_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `accum_op` decimal(20,2) NOT NULL DEFAULT '0.00',
  `accum_close` decimal(20,2) NOT NULL DEFAULT '0.00',
  `wdv` decimal(20,2) NOT NULL DEFAULT '0.00',
  `posting_date` date NOT NULL,
  `journal_no` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_journals`
--

CREATE TABLE `asset_journals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `asset_category_number` varchar(30) DEFAULT NULL,
  `asset_no` varchar(100) DEFAULT NULL,
  `asset_name` varchar(50) DEFAULT NULL,
  `periode` varchar(20) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `posting` tinyint(1) NOT NULL,
  `posting_by` varchar(30) DEFAULT NULL,
  `posting_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_journal_details`
--

CREATE TABLE `asset_journal_details` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `periode` varchar(10) NOT NULL,
  `asset_category_number` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `flag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `balance_sheets`
--

CREATE TABLE `balance_sheets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(10) NOT NULL,
  `index` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `amount` decimal(20,2) DEFAULT '0.00',
  `accumulated` decimal(30,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `balance_wip`
--

CREATE TABLE `balance_wip` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` date DEFAULT NULL,
  `item_rm_id` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barcode_divides`
--

CREATE TABLE `barcode_divides` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `reff` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `label_divided` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barcode_divides_fg`
--

CREATE TABLE `barcode_divides_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `reff` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `label_divided` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bc_kind`
--

CREATE TABLE `bc_kind` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `type` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `begin_wip`
--

CREATE TABLE `begin_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bom`
--

CREATE TABLE `bom` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `main_process` varchar(30) DEFAULT NULL,
  `sub_process` varchar(30) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `recyle` int(11) DEFAULT '0',
  `composition` decimal(20,8) DEFAULT '0.00000000',
  `drawing` text,
  `revision` int(11) NOT NULL DEFAULT '0',
  `remove` tinyint(1) DEFAULT '0',
  `remark` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bom_log`
--

CREATE TABLE `bom_log` (
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `main_process` varchar(30) DEFAULT NULL,
  `sub_process` varchar(30) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `recyle` int(11) DEFAULT '0',
  `composition` decimal(20,8) DEFAULT '0.00000000',
  `drawing` text,
  `revision` int(11) NOT NULL DEFAULT '0',
  `remove` tinyint(1) DEFAULT '0',
  `remark` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `calendars`
--

CREATE TABLE `calendars` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `working_date` date NOT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `chats`
--

CREATE TABLE `chats` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `from_users_id` varchar(30) NOT NULL,
  `to_users_id` varchar(30) NOT NULL,
  `messages` text NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheets`
--

CREATE TABLE `checksheets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `receipt` decimal(10,2) NOT NULL DEFAULT '0.00',
  `accumulate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cities`
--

CREATE TABLE `cities` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `country_id` varchar(20) NOT NULL,
  `state_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `config`
--

CREATE TABLE `config` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `address` text,
  `address2` text,
  `state` varchar(30) DEFAULT NULL,
  `logo` text,
  `favicon` text,
  `image` text,
  `theme` varchar(50) DEFAULT NULL,
  `tax` int(11) NOT NULL DEFAULT '0',
  `pph` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `fg_ss` int(11) NOT NULL DEFAULT '0',
  `cutoff_day_from` varchar(2) DEFAULT NULL,
  `cutoff_day_to` varchar(2) DEFAULT NULL,
  `wp_day_from` varchar(2) DEFAULT NULL,
  `wp_day_to` varchar(2) DEFAULT NULL,
  `cutoff_current` varchar(5) DEFAULT NULL,
  `cin_no` varchar(30) DEFAULT NULL,
  `gst_no` varchar(30) DEFAULT NULL,
  `pan_no` varchar(30) DEFAULT NULL,
  `pin_code` varchar(6) DEFAULT NULL,
  `si_terms` text,
  `iris_email` varchar(100) DEFAULT NULL,
  `iris_password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `config_iso`
--

CREATE TABLE `config_iso` (
  `doc_delivery_order` varchar(30) DEFAULT NULL,
  `doc_delivery_note` varchar(30) DEFAULT NULL,
  `doc_sales_invoice` varchar(30) DEFAULT NULL,
  `doc_packing_list` varchar(30) DEFAULT NULL,
  `doc_checksheet` varchar(30) DEFAULT NULL,
  `doc_purchase_request` varchar(30) DEFAULT NULL,
  `doc_purchase_order` varchar(30) DEFAULT NULL,
  `doc_job_order` varchar(30) DEFAULT NULL,
  `doc_supply_sheet` varchar(30) DEFAULT NULL,
  `doc_receiving_note` varchar(30) DEFAULT NULL,
  `doc_customer` varchar(30) DEFAULT NULL,
  `doc_material_requestion` varchar(30) DEFAULT NULL,
  `doc_ap_payment` varchar(30) DEFAULT NULL,
  `doc_ar_receipt` varchar(30) DEFAULT NULL,
  `form_delivery_order` varchar(30) DEFAULT NULL,
  `form_delivery_note` varchar(30) DEFAULT NULL,
  `form_sales_invoice` varchar(30) DEFAULT NULL,
  `form_packing_list` varchar(30) DEFAULT NULL,
  `form_checksheet` varchar(30) DEFAULT NULL,
  `form_purchase_request` varchar(30) DEFAULT NULL,
  `form_purchase_order` varchar(30) DEFAULT NULL,
  `form_job_order` varchar(30) DEFAULT NULL,
  `form_supply_sheet` varchar(30) DEFAULT NULL,
  `form_receiving_note` varchar(30) DEFAULT NULL,
  `form_customer` varchar(30) DEFAULT NULL,
  `form_material_requestion` varchar(30) DEFAULT NULL,
  `form_ap_payment` varchar(30) DEFAULT NULL,
  `form_ar_receipt` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `convertions`
--

CREATE TABLE `convertions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `uom_po` varchar(30) DEFAULT NULL,
  `uom_soft` varchar(30) DEFAULT NULL,
  `convertion` decimal(20,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `costing_components`
--

CREATE TABLE `costing_components` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) NOT NULL,
  `cost` decimal(20,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `costing_products`
--

CREATE TABLE `costing_products` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `umh` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty` decimal(20,2) DEFAULT '0.00',
  `total_qty` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_material` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_requestion` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_material_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_material_pcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_labor` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_overtime` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_labor_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_labor_pcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_foh` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_foh_pcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_total` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `costing_product_details`
--

CREATE TABLE `costing_product_details` (
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `supply_type` varchar(30) NOT NULL,
  `supply_date` date NOT NULL,
  `supply_no` varchar(30) DEFAULT NULL,
  `issued` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,4) DEFAULT '0.0000',
  `amount` decimal(20,4) DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `costing_product_workorders`
--

CREATE TABLE `costing_product_workorders` (
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `wp` int(11) NOT NULL DEFAULT '0',
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `umh` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_umh` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_labor` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_foh` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_material` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_overtime` decimal(20,2) NOT NULL DEFAULT '0.00',
  `direct_requestion` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `currencies`
--

CREATE TABLE `currencies` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `symbol` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `line_id` varchar(10) DEFAULT NULL,
  `line_no` varchar(10) DEFAULT NULL,
  `cin_no` varchar(30) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `currency` varchar(20) NOT NULL,
  `taxes` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_address`
--

CREATE TABLE `customer_address` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `department` varchar(30) DEFAULT NULL,
  `payment_term` int(11) NOT NULL DEFAULT '0',
  `gst_no` varchar(30) DEFAULT NULL,
  `vendor_code` varchar(30) DEFAULT NULL,
  `pan_no` varchar(30) DEFAULT NULL,
  `ifsc_code` varchar(30) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bill_address` text NOT NULL,
  `bill_country` varchar(30) DEFAULT NULL,
  `bill_state` varchar(30) DEFAULT NULL,
  `bill_city` varchar(30) DEFAULT NULL,
  `bill_contact` varchar(30) DEFAULT NULL,
  `bill_telp` varchar(30) DEFAULT NULL,
  `bill_fax` varchar(30) DEFAULT NULL,
  `bill_email` varchar(100) DEFAULT NULL,
  `bill_website` varchar(100) DEFAULT NULL,
  `bill_pin` varchar(6) DEFAULT NULL,
  `ship_pin` varchar(6) DEFAULT NULL,
  `ship_contact` varchar(30) DEFAULT NULL,
  `ship_telp` varchar(30) DEFAULT NULL,
  `ship_fax` varchar(30) DEFAULT NULL,
  `ship_email` varchar(100) DEFAULT NULL,
  `ship_website` varchar(100) DEFAULT NULL,
  `ship_address` text NOT NULL,
  `ship_country` varchar(30) DEFAULT NULL,
  `ship_state` varchar(30) DEFAULT NULL,
  `ship_city` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_items`
--

CREATE TABLE `customer_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `start_date` date DEFAULT NULL,
  `valid_date` date DEFAULT NULL,
  `attachment` text,
  `remark` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_item_histories`
--

CREATE TABLE `customer_item_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `start_date` date DEFAULT NULL,
  `valid_date` date DEFAULT NULL,
  `attachment` text,
  `remark` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_notes`
--

CREATE TABLE `delivery_notes` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `customer_address_id` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_no` varchar(30) DEFAULT NULL,
  `delivery_note_no` varchar(30) DEFAULT NULL,
  `delivery_note_date` date DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `ship_address` varchar(100) DEFAULT NULL,
  `origin` varchar(30) DEFAULT NULL,
  `sailing` varchar(30) DEFAULT NULL,
  `ship_by` varchar(30) DEFAULT NULL,
  `vehicle_no` varchar(30) DEFAULT NULL,
  `incoterm` varchar(30) DEFAULT NULL,
  `note` text,
  `uom` varchar(20) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT '0',
  `status_delivery` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_orders`
--

CREATE TABLE `delivery_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `trans_type` varchar(10) DEFAULT NULL,
  `remarks` text,
  `uom` varchar(20) DEFAULT NULL,
  `qty_so` int(11) NOT NULL DEFAULT '0',
  `qty_remain` int(11) NOT NULL DEFAULT '0',
  `qty_do` int(11) NOT NULL DEFAULT '0',
  `qty_del` int(11) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '0',
  `stock_bal` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_schedules`
--

CREATE TABLE `delivery_schedules` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `po_no` varchar(30) DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(50) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_1` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_2` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_3` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_4` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_5` int(11) NOT NULL DEFAULT '0',
  `delivey_qty_6` int(11) NOT NULL DEFAULT '0',
  `delivey_date_1` date DEFAULT NULL,
  `delivey_date_2` date DEFAULT NULL,
  `delivey_date_3` date DEFAULT NULL,
  `delivey_date_4` date DEFAULT NULL,
  `delivey_date_5` date DEFAULT NULL,
  `delivey_date_6` date DEFAULT NULL,
  `etd_1` date DEFAULT NULL,
  `etd_2` date DEFAULT NULL,
  `etd_3` date DEFAULT NULL,
  `etd_4` date DEFAULT NULL,
  `etd_5` date DEFAULT NULL,
  `etd_6` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `departements`
--

CREATE TABLE `departements` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dol_items`
--

CREATE TABLE `dol_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `mpq` int(11) DEFAULT '0',
  `category` varchar(10) DEFAULT 'RM',
  `is_dol` tinyint(1) DEFAULT NULL,
  `reorder_point` int(11) DEFAULT '0',
  `lead_time_days` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT NULL,
  `status` varchar(2) DEFAULT '00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dol_items_history`
--

CREATE TABLE `dol_items_history` (
  `id` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `item_rm_id` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `mpq` int(11) DEFAULT '0',
  `category` varchar(10) CHARACTER SET utf8mb4 DEFAULT 'RM',
  `is_dol` tinyint(1) DEFAULT NULL,
  `reorder_point` int(11) DEFAULT '0',
  `lead_time_days` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT NULL,
  `status` varchar(2) CHARACTER SET utf8mb4 DEFAULT '00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ecn`
--

CREATE TABLE `ecn` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `main_process` varchar(30) DEFAULT NULL,
  `sub_process` varchar(30) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `recyle` int(11) DEFAULT '0',
  `composition` decimal(20,8) DEFAULT '0.00000000',
  `composition_old` decimal(20,8) DEFAULT '0.00000000',
  `revision` int(11) NOT NULL DEFAULT '0',
  `remark` text,
  `plant` varchar(30) NOT NULL DEFAULT 'BWD,NOD',
  `status` varchar(10) DEFAULT 'SAME',
  `cancel` varchar(3) DEFAULT 'NO',
  `cancel_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ecn_comment`
--

CREATE TABLE `ecn_comment` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `currency_from` varchar(5) DEFAULT NULL,
  `currency_to` varchar(5) DEFAULT NULL,
  `selling` decimal(20,4) DEFAULT '0.0000',
  `buying` decimal(20,4) DEFAULT '0.0000',
  `middle` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `forecasts`
--

CREATE TABLE `forecasts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `attachment` text,
  `issued_date` date DEFAULT NULL,
  `p_month` varchar(10) DEFAULT NULL,
  `p_year` varchar(10) DEFAULT NULL,
  `revision` int(1) DEFAULT '0',
  `month_1` int(11) DEFAULT '0',
  `month_2` int(11) DEFAULT '0',
  `month_3` int(11) DEFAULT '0',
  `month_4` int(11) DEFAULT '0',
  `month_5` int(11) DEFAULT '0',
  `month_6` int(11) DEFAULT '0',
  `month_7` int(11) DEFAULT '0',
  `month_8` int(11) DEFAULT '0',
  `month_9` int(11) DEFAULT '0',
  `month_10` int(11) DEFAULT '0',
  `month_11` int(11) DEFAULT '0',
  `month_12` int(11) DEFAULT '0',
  `remark` text,
  `reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `forecast_histories`
--

CREATE TABLE `forecast_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `p_month` varchar(10) DEFAULT NULL,
  `p_year` varchar(10) DEFAULT NULL,
  `revision` int(1) DEFAULT '0',
  `month_1` int(11) DEFAULT '0',
  `month_2` int(11) DEFAULT '0',
  `month_3` int(11) DEFAULT '0',
  `month_4` int(11) DEFAULT '0',
  `month_5` int(11) DEFAULT '0',
  `month_6` int(11) DEFAULT '0',
  `month_7` int(11) DEFAULT '0',
  `month_8` int(11) DEFAULT '0',
  `month_9` int(11) DEFAULT '0',
  `month_10` int(11) DEFAULT '0',
  `month_11` int(11) DEFAULT '0',
  `month_12` int(11) DEFAULT '0',
  `remark` text,
  `reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_item_calculation`
--

CREATE TABLE `generate_item_calculation` (
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `item_rm_id` varchar(50) DEFAULT NULL,
  `date_1` varchar(11) DEFAULT '0',
  `date_2` varchar(11) DEFAULT '0',
  `date_3` varchar(11) DEFAULT '0',
  `date_4` varchar(11) DEFAULT '0',
  `date_5` varchar(11) DEFAULT '0',
  `date_6` varchar(11) DEFAULT '0',
  `date_7` varchar(11) DEFAULT '0',
  `date_8` varchar(11) DEFAULT '0',
  `date_9` varchar(11) DEFAULT '0',
  `date_10` varchar(11) DEFAULT '0',
  `date_11` varchar(11) DEFAULT '0',
  `date_12` varchar(11) DEFAULT '0',
  `date_13` varchar(11) DEFAULT '0',
  `date_14` varchar(11) DEFAULT '0',
  `date_15` varchar(11) DEFAULT '0',
  `date_16` varchar(11) DEFAULT '0',
  `date_17` varchar(11) DEFAULT '0',
  `date_18` varchar(11) DEFAULT '0',
  `date_19` varchar(11) DEFAULT '0',
  `date_20` varchar(11) DEFAULT '0',
  `date_21` varchar(11) DEFAULT '0',
  `date_22` varchar(11) DEFAULT '0',
  `date_23` varchar(11) DEFAULT '0',
  `date_24` varchar(11) DEFAULT '0',
  `date_25` varchar(11) DEFAULT '0',
  `date_26` varchar(11) DEFAULT '0',
  `date_27` varchar(11) DEFAULT '0',
  `date_28` varchar(11) DEFAULT '0',
  `date_29` varchar(11) DEFAULT '0',
  `date_30` varchar(11) DEFAULT '0',
  `date_31` varchar(11) DEFAULT '0',
  `total` decimal(20,4) DEFAULT '0.0000',
  `balance` decimal(20,4) DEFAULT '0.0000',
  `os_po` decimal(20,4) DEFAULT '0.0000',
  `os_supply` int(11) NOT NULL DEFAULT '0',
  `os_wo` int(11) NOT NULL DEFAULT '0',
  `balance_wip` int(11) NOT NULL DEFAULT '0',
  `eta` int(11) NOT NULL DEFAULT '0',
  `stock_whs` int(11) NOT NULL DEFAULT '0',
  `stock_hand` int(11) NOT NULL,
  `need_2` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_loadcap`
--

CREATE TABLE `generate_loadcap` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `customer_name` varchar(50) DEFAULT NULL,
  `month_1` int(11) DEFAULT '0',
  `month_2` int(11) DEFAULT '0',
  `month_3` int(11) DEFAULT '0',
  `month_4` int(11) DEFAULT '0',
  `month_5` int(11) DEFAULT '0',
  `month_6` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_loadcap_line`
--

CREATE TABLE `generate_loadcap_line` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `shift` int(11) NOT NULL DEFAULT '0',
  `overtime` int(11) NOT NULL DEFAULT '0',
  `line_no` varchar(30) DEFAULT NULL,
  `process_main_number` varchar(30) DEFAULT NULL,
  `process_main_name` varchar(50) DEFAULT NULL,
  `process_sub_number` varchar(30) DEFAULT NULL,
  `process_sub_name` varchar(50) DEFAULT NULL,
  `machine_qty` int(11) NOT NULL DEFAULT '0',
  `efficiency` decimal(10,2) NOT NULL DEFAULT '0.00',
  `month_1` int(11) DEFAULT '0',
  `month_2` int(11) DEFAULT '0',
  `month_3` int(11) DEFAULT '0',
  `month_4` int(11) DEFAULT '0',
  `month_5` int(11) DEFAULT '0',
  `month_6` int(11) DEFAULT '0',
  `capacity_1` int(11) NOT NULL DEFAULT '0',
  `capacity_2` int(11) NOT NULL DEFAULT '0',
  `capacity_3` int(11) NOT NULL DEFAULT '0',
  `capacity_4` int(11) NOT NULL DEFAULT '0',
  `capacity_5` int(11) NOT NULL DEFAULT '0',
  `capacity_6` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_loadcap_product`
--

CREATE TABLE `generate_loadcap_product` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `month_1_prodplan` int(11) DEFAULT '0',
  `month_1_umh` decimal(11,5) DEFAULT '0.00000',
  `month_1_total` decimal(11,2) DEFAULT '0.00',
  `month_2_prodplan` int(11) DEFAULT '0',
  `month_2_umh` decimal(11,5) DEFAULT '0.00000',
  `month_2_total` decimal(11,2) DEFAULT '0.00',
  `month_3_prodplan` int(11) DEFAULT '0',
  `month_3_umh` decimal(11,5) DEFAULT '0.00000',
  `month_3_total` decimal(11,2) DEFAULT '0.00',
  `month_4_prodplan` int(11) DEFAULT '0',
  `month_4_umh` decimal(11,5) DEFAULT '0.00000',
  `month_4_total` decimal(11,2) DEFAULT '0.00',
  `month_5_prodplan` int(11) DEFAULT '0',
  `month_5_umh` decimal(11,5) DEFAULT '0.00000',
  `month_5_total` decimal(11,2) DEFAULT '0.00',
  `month_6_prodplan` int(11) DEFAULT '0',
  `month_6_umh` decimal(11,5) DEFAULT '0.00000',
  `month_6_total` decimal(11,2) DEFAULT '0.00',
  `status` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_machine_capacity`
--

CREATE TABLE `generate_machine_capacity` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `shift` varchar(1) DEFAULT NULL,
  `overtime` varchar(1) DEFAULT NULL,
  `process_main_number` varchar(30) DEFAULT NULL,
  `process_main_name` varchar(30) DEFAULT NULL,
  `process_sub_number` varchar(30) DEFAULT NULL,
  `process_sub_name` varchar(30) DEFAULT NULL,
  `machine_qty` decimal(20,2) DEFAULT '0.00',
  `efficiency` decimal(20,2) DEFAULT '0.00',
  `capacity` decimal(20,2) DEFAULT '0.00',
  `date_1` varchar(11) DEFAULT '0',
  `date_2` varchar(11) DEFAULT '0',
  `date_3` varchar(11) DEFAULT '0',
  `date_4` varchar(11) DEFAULT '0',
  `date_5` varchar(11) DEFAULT '0',
  `date_6` varchar(11) DEFAULT '0',
  `date_7` varchar(11) DEFAULT '0',
  `date_8` varchar(11) DEFAULT '0',
  `date_9` varchar(11) DEFAULT '0',
  `date_10` varchar(11) DEFAULT '0',
  `date_11` varchar(11) DEFAULT '0',
  `date_12` varchar(11) DEFAULT '0',
  `date_13` varchar(11) DEFAULT '0',
  `date_14` varchar(11) DEFAULT '0',
  `date_15` varchar(11) DEFAULT '0',
  `date_16` varchar(11) DEFAULT '0',
  `date_17` varchar(11) DEFAULT '0',
  `date_18` varchar(11) DEFAULT '0',
  `date_19` varchar(11) DEFAULT '0',
  `date_20` varchar(11) DEFAULT '0',
  `date_21` varchar(11) DEFAULT '0',
  `date_22` varchar(11) DEFAULT '0',
  `date_23` varchar(11) DEFAULT '0',
  `date_24` varchar(11) DEFAULT '0',
  `date_25` varchar(11) DEFAULT '0',
  `date_26` varchar(11) DEFAULT '0',
  `date_27` varchar(11) DEFAULT '0',
  `date_28` varchar(11) DEFAULT '0',
  `date_29` varchar(11) DEFAULT '0',
  `date_30` varchar(11) DEFAULT '0',
  `date_31` varchar(11) DEFAULT '0',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mpp`
--

CREATE TABLE `generate_mpp` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(50) DEFAULT NULL,
  `circuit_no` varchar(10) DEFAULT NULL,
  `prod_plan` int(11) DEFAULT '0',
  `date_1` varchar(11) DEFAULT '0',
  `date_2` varchar(11) DEFAULT '0',
  `date_3` varchar(11) DEFAULT '0',
  `date_4` varchar(11) DEFAULT '0',
  `date_5` varchar(11) DEFAULT '0',
  `date_6` varchar(11) DEFAULT '0',
  `date_7` varchar(11) DEFAULT '0',
  `date_8` varchar(11) DEFAULT '0',
  `date_9` varchar(11) DEFAULT '0',
  `date_10` varchar(11) DEFAULT '0',
  `date_11` varchar(11) DEFAULT '0',
  `date_12` varchar(11) DEFAULT '0',
  `date_13` varchar(11) DEFAULT '0',
  `date_14` varchar(11) DEFAULT '0',
  `date_15` varchar(11) DEFAULT '0',
  `date_16` varchar(11) DEFAULT '0',
  `date_17` varchar(11) DEFAULT '0',
  `date_18` varchar(11) DEFAULT '0',
  `date_19` varchar(11) DEFAULT '0',
  `date_20` varchar(11) DEFAULT '0',
  `date_21` varchar(11) DEFAULT '0',
  `date_22` varchar(11) DEFAULT '0',
  `date_23` varchar(11) DEFAULT '0',
  `date_24` varchar(11) DEFAULT '0',
  `date_25` varchar(11) DEFAULT '0',
  `date_26` varchar(11) DEFAULT '0',
  `date_27` varchar(11) DEFAULT '0',
  `date_28` varchar(11) DEFAULT '0',
  `date_29` varchar(11) DEFAULT '0',
  `date_30` varchar(11) DEFAULT '0',
  `date_31` varchar(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mps`
--

CREATE TABLE `generate_mps` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `cutoff` timestamp NULL DEFAULT NULL,
  `wip_month` varchar(50) DEFAULT NULL,
  `pp` int(11) DEFAULT '0',
  `wc` int(11) NOT NULL DEFAULT '0',
  `ha` int(11) NOT NULL DEFAULT '0',
  `c` int(11) NOT NULL DEFAULT '0',
  `v` int(11) NOT NULL DEFAULT '0',
  `p` int(11) NOT NULL DEFAULT '0',
  `rfg` int(11) NOT NULL DEFAULT '0',
  `fg` int(11) DEFAULT '0',
  `os_mpp` int(11) DEFAULT '0',
  `os_so` int(11) DEFAULT '0',
  `total_stock` int(11) DEFAULT '0',
  `balance` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mps_details`
--

CREATE TABLE `generate_mps_details` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `ltpp_month` varchar(50) DEFAULT NULL,
  `ltpp_month2` date DEFAULT NULL,
  `hkw` int(11) DEFAULT '0',
  `begin_balance` int(11) DEFAULT '0',
  `ito` int(11) DEFAULT '0',
  `so` int(11) NOT NULL DEFAULT '0',
  `forecast` int(11) DEFAULT '0',
  `delivery_rate` int(11) DEFAULT '0',
  `safety_stock` int(11) DEFAULT '0',
  `safety_stock_reduce` int(11) DEFAULT '0',
  `prod_plan` int(11) DEFAULT '0',
  `need` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mrp`
--

CREATE TABLE `generate_mrp` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` timestamp NULL DEFAULT NULL,
  `period` date DEFAULT NULL,
  `prodplan` int(11) DEFAULT '0',
  `item_rm_id` varchar(50) DEFAULT NULL,
  `qpa` decimal(10,5) DEFAULT '0.00000',
  `qty` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mrp_abcclass`
--

CREATE TABLE `generate_mrp_abcclass` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `item_rm_id` varchar(50) DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `need` int(11) NOT NULL DEFAULT '0',
  `total` int(11) NOT NULL DEFAULT '0',
  `composition` decimal(10,2) DEFAULT '0.00',
  `class` varchar(10) DEFAULT NULL,
  `safety` int(11) DEFAULT '0',
  `safety_stock` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mrp_finals`
--

CREATE TABLE `generate_mrp_finals` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT '',
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` timestamp NULL DEFAULT NULL,
  `item_rm_id` varchar(50) DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `share_order` int(11) NOT NULL DEFAULT '0',
  `mpq` int(11) DEFAULT '0',
  `moq` int(11) DEFAULT '0',
  `leadtime` int(11) DEFAULT '0',
  `issued_1` decimal(20,2) DEFAULT '0.00',
  `issued_2` decimal(20,2) DEFAULT '0.00',
  `issued_3` decimal(20,2) DEFAULT '0.00',
  `issued_avg` decimal(20,2) DEFAULT '0.00',
  `qty_rm` decimal(20,2) DEFAULT '0.00',
  `qty_wip` decimal(20,2) DEFAULT '0.00',
  `qty_vendor` decimal(20,2) DEFAULT '0.00',
  `qty_gate` decimal(20,2) DEFAULT '0.00',
  `total_stock` decimal(20,2) DEFAULT '0.00',
  `qty_supply` decimal(20,2) DEFAULT '0.00',
  `qty_wo` decimal(20,2) DEFAULT '0.00',
  `total_wo` decimal(20,2) DEFAULT '0.00',
  `need_1` decimal(20,2) DEFAULT '0.00',
  `need_2` decimal(20,2) DEFAULT '0.00',
  `need_3` decimal(20,2) DEFAULT '0.00',
  `need_4` decimal(20,2) DEFAULT '0.00',
  `need_5` decimal(20,2) DEFAULT '0.00',
  `need_6` decimal(20,2) DEFAULT '0.00',
  `balance_1` decimal(20,2) DEFAULT '0.00',
  `balance_2` decimal(20,2) DEFAULT '0.00',
  `balance_3` decimal(20,2) DEFAULT '0.00',
  `balance_4` decimal(20,2) DEFAULT '0.00',
  `balance_5` decimal(20,2) DEFAULT '0.00',
  `balance_6` decimal(20,2) DEFAULT '0.00',
  `avg_need` decimal(20,2) NOT NULL DEFAULT '0.00',
  `avg_balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `safety_stock` int(11) NOT NULL DEFAULT '0',
  `safety_stock_persen` decimal(20,2) NOT NULL DEFAULT '0.00',
  `share_order_qty` int(11) NOT NULL DEFAULT '0',
  `total_need` decimal(20,2) DEFAULT '0.00',
  `purchase_order` decimal(20,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `harnes_types`
--

CREATE TABLE `harnes_types` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `hsn`
--

CREATE TABLE `hsn` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `igst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(20,2) DEFAULT '0.00',
  `cgst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `income_statements`
--

CREATE TABLE `income_statements` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(10) NOT NULL,
  `index` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `amount` decimal(20,2) DEFAULT '0.00',
  `accumulated` decimal(30,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `incoterms`
--

CREATE TABLE `incoterms` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_fg`
--

CREATE TABLE `inventory_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(20) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `trans_type` varchar(20) DEFAULT NULL,
  `type_sales` varchar(20) DEFAULT NULL,
  `created_name` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `document_no` varchar(100) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `direct_material` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `direct_labor` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `direct_foh` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `price` decimal(20,10) DEFAULT '0.0000000000',
  `amount` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_rm`
--

CREATE TABLE `inventory_rm` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(20) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `item_family_id` varchar(30) NOT NULL,
  `trans_type` varchar(20) DEFAULT NULL,
  `created_name` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `custom_kind` varchar(30) DEFAULT NULL,
  `custom_no` varchar(100) DEFAULT NULL,
  `document_no` varchar(100) DEFAULT NULL,
  `custom_date` varchar(10) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `amount` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_wip`
--

CREATE TABLE `inventory_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(20) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `trans_type` varchar(20) DEFAULT NULL,
  `created_name` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `document_no` varchar(100) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `direct_material` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `direct_labor` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `direct_foh` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `price` decimal(20,10) DEFAULT '0.0000000000',
  `amount` decimal(20,10) NOT NULL DEFAULT '0.0000000000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_materials`
--

CREATE TABLE `issued_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(50) NOT NULL,
  `item_rm_id` varchar(50) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_material_details`
--

CREATE TABLE `issued_material_details` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `request_no` varchar(50) NOT NULL,
  `label_no` varchar(50) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(20,5) DEFAULT '0.00000',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_others`
--

CREATE TABLE `issued_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(50) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `por_no` varchar(30) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `balance` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,2) DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL,
  `out_by` varchar(30) DEFAULT NULL,
  `out_date` date DEFAULT NULL,
  `reason` text,
  `location_other_number` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_others_request`
--

CREATE TABLE `issued_others_request` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(50) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `por_no` varchar(30) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,4) DEFAULT '0.0000',
  `balance` int(11) NOT NULL DEFAULT '0',
  `remarks` text,
  `status` tinyint(1) NOT NULL,
  `reason` text,
  `location_other_number` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_others_store`
--

CREATE TABLE `issued_others_store` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `issued_others_request_no` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(50) NOT NULL,
  `document_in` varchar(30) DEFAULT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `por_no` varchar(30) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `balance` int(11) NOT NULL DEFAULT '0',
  `remarks` text,
  `status` tinyint(1) NOT NULL,
  `out_by` varchar(30) DEFAULT NULL,
  `out_date` date DEFAULT NULL,
  `reason` text,
  `location_other_number` varchar(30) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_categories`
--

CREATE TABLE `item_categories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `type` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_colors`
--

CREATE TABLE `item_colors` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `code` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_familys`
--

CREATE TABLE `item_familys` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `usefull` int(11) NOT NULL DEFAULT '0',
  `shift` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_family_subs`
--

CREATE TABLE `item_family_subs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_fg`
--

CREATE TABLE `item_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `harnes_type` varchar(30) DEFAULT NULL,
  `number` varchar(30) NOT NULL,
  `number_customer` varchar(30) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `cct` int(11) NOT NULL DEFAULT '0',
  `polybag` varchar(30) DEFAULT NULL,
  `box_label` varchar(10) DEFAULT NULL,
  `ng_ration` decimal(20,2) DEFAULT '0.00',
  `is_no` varchar(30) DEFAULT NULL,
  `weight` decimal(20,5) DEFAULT '0.00000',
  `leadtime` int(11) DEFAULT '0',
  `mpq` int(11) DEFAULT '0',
  `moq` int(11) DEFAULT '0',
  `uom` varchar(10) DEFAULT NULL,
  `qty_box` int(11) DEFAULT '0',
  `safety_stock` decimal(20,2) NOT NULL DEFAULT '0.00',
  `attachment` text,
  `status` tinyint(1) NOT NULL,
  `alias` varchar(30) DEFAULT NULL,
  `logo` tinyint(1) NOT NULL,
  `control_id` varchar(30) DEFAULT NULL,
  `box_sub` int(11) NOT NULL DEFAULT '0',
  `lot` int(11) DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'LOCAL',
  `hsn_code` varchar(20) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `revision` varchar(10) NOT NULL DEFAULT '01',
  `foto` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_kinds`
--

CREATE TABLE `item_kinds` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) NOT NULL,
  `item_family_id` varchar(30) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_mpq`
--

CREATE TABLE `item_mpq` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `mpq` int(11) DEFAULT NULL,
  `calculate` varchar(10) DEFAULT 'YES',
  `status` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_ng`
--

CREATE TABLE `item_ng` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `process_id` varchar(30) NOT NULL,
  `process` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `stock` decimal(20,2) NOT NULL DEFAULT '0.00',
  `issued` decimal(20,2) DEFAULT '0.00',
  `wip` decimal(20,2) DEFAULT '0.00',
  `qty` decimal(11,2) NOT NULL DEFAULT '0.00',
  `scrap` decimal(20,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `uom` varchar(10) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_platting`
--

CREATE TABLE `item_platting` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `code` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_rm`
--

CREATE TABLE `item_rm` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_sub_family_id` varchar(30) DEFAULT NULL,
  `maker_id` varchar(30) DEFAULT NULL,
  `number` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `kind` varchar(30) NOT NULL,
  `size` varchar(30) NOT NULL,
  `color_1` varchar(30) NOT NULL,
  `color_2` varchar(30) NOT NULL,
  `male` varchar(30) NOT NULL,
  `waterproof` varchar(30) NOT NULL,
  `platting` varchar(30) NOT NULL,
  `uom` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `supply` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'LOCAL',
  `hsn_code` varchar(30) DEFAULT NULL,
  `foto` text,
  `status` tinyint(1) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_sizes`
--

CREATE TABLE `item_sizes` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) NOT NULL,
  `item_family_id` varchar(30) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_clearings`
--

CREATE TABLE `journal_clearings` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_to` varchar(50) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `journal_date` date DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `document_date` date DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `company_id` varchar(30) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `description` text,
  `remarks` text,
  `source` varchar(10) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `adjustment` decimal(20,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_postings`
--

CREATE TABLE `journal_postings` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_to` varchar(50) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `journal_date` date DEFAULT NULL,
  `journal_type` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `modul` varchar(30) DEFAULT NULL,
  `remarks` text,
  `document_no` varchar(30) DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `number` varchar(15) DEFAULT NULL,
  `company_id` varchar(30) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `description` text,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `original_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `original_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rates` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `local_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `local_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `posting` tinyint(1) NOT NULL DEFAULT '0',
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `tds` varchar(30) DEFAULT NULL,
  `taxes` varchar(20) DEFAULT NULL,
  `taxes_amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `debitcredit` varchar(10) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `status_cl` tinyint(4) NOT NULL DEFAULT '0',
  `status_bank` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_revaluations`
--

CREATE TABLE `journal_revaluations` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `period` varchar(20) DEFAULT NULL,
  `modul` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(20,2) NOT NULL DEFAULT '0.00',
  `revaluation` decimal(20,2) NOT NULL DEFAULT '0.00',
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_setups`
--

CREATE TABLE `journal_setups` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `ap_payment` varchar(5) DEFAULT 'NO',
  `ar_receipt` varchar(5) NOT NULL DEFAULT 'NO',
  `flag` tinyint(4) NOT NULL DEFAULT '0',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_types`
--

CREATE TABLE `journal_types` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(10) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `module` text,
  `module_type` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `inventory` varchar(3) DEFAULT 'NO',
  `inventory_process` varchar(4) DEFAULT 'NONE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `line_productions`
--

CREATE TABLE `line_productions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logins`
--

CREATE TABLE `logins` (
  `id` bigint(30) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `mac_address` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `action` varchar(30) NOT NULL,
  `menu` varchar(30) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `machines`
--

CREATE TABLE `machines` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `specification` text,
  `process_main_number` varchar(30) DEFAULT NULL,
  `process_sub_number` varchar(30) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `maker` varchar(30) DEFAULT NULL,
  `toonage` int(11) DEFAULT '0',
  `tiebar` varchar(30) DEFAULT NULL,
  `uom_tiebar` varchar(10) DEFAULT NULL,
  `min_closing` int(11) DEFAULT '0',
  `uom_min` varchar(10) DEFAULT NULL,
  `max_open` int(11) DEFAULT '0',
  `uom_max` varchar(10) DEFAULT NULL,
  `volume` int(11) DEFAULT '0',
  `uom_volume` varchar(10) DEFAULT NULL,
  `diameter` int(11) DEFAULT '0',
  `uom_diameter` varchar(10) DEFAULT NULL,
  `brand` varchar(10) DEFAULT NULL,
  `foto` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `makers`
--

CREATE TABLE `makers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `type` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `menus_id` varchar(30) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `description` text,
  `link` text NOT NULL,
  `sort` int(11) NOT NULL,
  `icon` varchar(30) NOT NULL,
  `flag` varchar(10) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `accounting` varchar(10) DEFAULT 'NO',
  `financial` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus_no`
--

CREATE TABLE `menus_no` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `menus_id` varchar(30) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `start_date` varchar(30) NOT NULL,
  `end_date` varchar(30) NOT NULL,
  `sequence` int(11) NOT NULL,
  `format` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `new_barcode`
--

CREATE TABLE `new_barcode` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT '0',
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `cut_off_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `new_barcode_fg`
--

CREATE TABLE `new_barcode_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT '0',
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `cut_off_date` date DEFAULT NULL,
  `prod_date` date NOT NULL,
  `packing_date` date NOT NULL,
  `qc_1` varchar(30) DEFAULT NULL,
  `qcnumber_1` varchar(30) DEFAULT NULL,
  `qc_2` varchar(30) DEFAULT NULL,
  `qcnumber_2` varchar(30) DEFAULT NULL,
  `op_1` varchar(30) DEFAULT NULL,
  `opnumber_1` varchar(30) DEFAULT NULL,
  `op_2` varchar(30) DEFAULT NULL,
  `opnumber_2` varchar(30) DEFAULT NULL,
  `shift` tinyint(1) DEFAULT NULL,
  `packing` tinyint(1) DEFAULT NULL,
  `packing_qty` int(11) NOT NULL DEFAULT '0',
  `lot_no` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ng_types`
--

CREATE TABLE `ng_types` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `process_main_id` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `users_id_from` varchar(30) DEFAULT NULL,
  `users_id_to` varchar(30) DEFAULT NULL,
  `table_id` varchar(30) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `logs` text,
  `reason` text,
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `origins`
--

CREATE TABLE `origins` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_fg`
--

CREATE TABLE `os_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `remarks` varchar(100) DEFAULT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'BEGIN',
  `created_label` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_po`
--

CREATE TABLE `os_po` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` date DEFAULT NULL,
  `item_rm_id` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `actual` int(11) DEFAULT '0',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_rm`
--

CREATE TABLE `os_rm` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,4) DEFAULT '0.0000',
  `currency` varchar(3) DEFAULT 'INR',
  `type` varchar(10) NOT NULL DEFAULT 'BEGIN',
  `category` varchar(10) DEFAULT 'IN',
  `created_label` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_rm_labels`
--

CREATE TABLE `os_rm_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) NOT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `price` decimal(20,5) DEFAULT '0.00000',
  `label_from` varchar(30) DEFAULT NULL,
  `warehouse` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_supply`
--

CREATE TABLE `os_supply` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` date DEFAULT NULL,
  `item_rm_id` varchar(100) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `actual` decimal(10,2) DEFAULT '0.00',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_wo`
--

CREATE TABLE `os_wo` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` date DEFAULT NULL,
  `item_rm_id` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `actual` int(11) DEFAULT '0',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `packing_lists`
--

CREATE TABLE `packing_lists` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `dn_number` varchar(50) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pallet_no` decimal(10,2) DEFAULT '0.00',
  `carton` decimal(10,2) DEFAULT '0.00',
  `net_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gross_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `measure` decimal(10,2) DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_terms`
--

CREATE TABLE `payment_terms` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `planning_config`
--

CREATE TABLE `planning_config` (
  `fg_ss` int(11) NOT NULL DEFAULT '0',
  `cutoff_day_from` varchar(2) DEFAULT NULL,
  `cutoff_day_to` varchar(2) DEFAULT NULL,
  `wp_day_from` varchar(2) DEFAULT NULL,
  `wp_day_to` varchar(2) DEFAULT NULL,
  `cutoff_current` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `process`
--

CREATE TABLE `process` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `process_main_id` varchar(30) DEFAULT NULL,
  `process_sub_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `efficiency` decimal(20,2) NOT NULL DEFAULT '0.00',
  `mp` int(11) NOT NULL DEFAULT '0',
  `flag` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `process_capacity`
--

CREATE TABLE `process_capacity` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `process_main_number` varchar(30) DEFAULT NULL,
  `process_sub_number` varchar(30) DEFAULT NULL,
  `process_number` varchar(30) DEFAULT NULL,
  `manpower` int(11) NOT NULL DEFAULT '0',
  `efficiency` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `process_main`
--

CREATE TABLE `process_main` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('PP','HAV') NOT NULL DEFAULT 'PP',
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `process_main_sub`
--

CREATE TABLE `process_main_sub` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `process_main_id` varchar(30) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `efficiency` int(11) NOT NULL DEFAULT '0',
  `station` int(11) NOT NULL DEFAULT '0',
  `station_mp` int(11) NOT NULL DEFAULT '0',
  `mp_actual` int(11) NOT NULL DEFAULT '0',
  `flag` int(11) NOT NULL DEFAULT '0',
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `production_schedules`
--

CREATE TABLE `production_schedules` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL,
  `status_wip` int(11) NOT NULL DEFAULT '0',
  `status_input` varchar(10) NOT NULL DEFAULT 'MPP',
  `precut_status` varchar(5) NOT NULL DEFAULT 'NO',
  `precut_by` varchar(30) DEFAULT NULL,
  `precut_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_confirm`
--

CREATE TABLE `purchase_confirm` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `delivery` int(11) DEFAULT '0',
  `price` decimal(20,5) DEFAULT NULL,
  `total` decimal(20,5) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `etd` date DEFAULT NULL,
  `delivery_no` varchar(30) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `awb_no` varchar(30) DEFAULT NULL,
  `awb_date` date DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_confirm_others`
--

CREATE TABLE `purchase_confirm_others` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `delivery` decimal(20,2) DEFAULT '0.00',
  `uom` varchar(10) DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `etd` date DEFAULT NULL,
  `delivery_no` varchar(30) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `awb_no` varchar(30) DEFAULT NULL,
  `awb_date` date DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `boe_no` varchar(30) DEFAULT NULL,
  `boe_date` date DEFAULT NULL,
  `taxes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pph` int(11) NOT NULL DEFAULT '0',
  `payment_term` int(10) NOT NULL DEFAULT '0',
  `currency` varchar(10) DEFAULT NULL,
  `total_discount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_vat` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_pph` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_dp` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_tds` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_tcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_freight` decimal(20,2) NOT NULL DEFAULT '0.00',
  `tcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `type` varchar(10) DEFAULT NULL,
  `remarks` text,
  `voucher` varchar(20) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `por_no` varchar(50) DEFAULT NULL,
  `asset_code` varchar(30) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `uom` varchar(20) DEFAULT NULL,
  `price` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `cgst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `charge` int(11) DEFAULT '0',
  `charge_amount` decimal(20,2) DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total2` decimal(20,2) NOT NULL,
  `total_idr` decimal(20,2) NOT NULL DEFAULT '0.00',
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `flag` varchar(10) DEFAULT 'PURCHASE',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_invoice_journals`
--

CREATE TABLE `purchase_invoice_journals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `flag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` int(1) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(11) NOT NULL DEFAULT '0',
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `price` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `total` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `taxes` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_vat` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_dp` decimal(20,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) DEFAULT NULL,
  `leadtime` int(11) NOT NULL DEFAULT '0',
  `remarks` text,
  `reason` text,
  `rev` int(11) NOT NULL DEFAULT '0',
  `price_old` decimal(20,4) DEFAULT '0.0000',
  `sync_by` varchar(30) DEFAULT NULL,
  `sync_date` timestamp NULL DEFAULT NULL,
  `complete` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `status_edit` tinyint(4) NOT NULL DEFAULT '0',
  `total_sub` decimal(20,2) NOT NULL DEFAULT '0.00',
  `income_tax` decimal(20,2) NOT NULL DEFAULT '0.00',
  `income_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_grand` decimal(20,2) NOT NULL DEFAULT '0.00',
  `disc_pr` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `month_1` int(11) DEFAULT '0',
  `month_2` int(11) DEFAULT '0',
  `month_3` int(11) DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'PO',
  `push_date` timestamp NULL DEFAULT NULL,
  `push` varchar(10) DEFAULT NULL,
  `log` text,
  `note` text,
  `terms` text,
  `incoterm` varchar(100) DEFAULT NULL,
  `payment_term` int(11) DEFAULT '0',
  `attachment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_labels`
--

CREATE TABLE `purchase_order_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(2) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `lot_no` varchar(20) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_others`
--

CREATE TABLE `purchase_order_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `asset_code` varchar(20) DEFAULT NULL,
  `asset_code_sub` varchar(30) DEFAULT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `maker` varchar(50) DEFAULT NULL,
  `terms` text,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `price` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `total` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `taxes` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_dp` decimal(20,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `revision` int(11) NOT NULL DEFAULT '0',
  `type` varchar(2) DEFAULT 'PO',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_receipts`
--

CREATE TABLE `purchase_order_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `bc_kind` varchar(30) DEFAULT NULL,
  `bc_aju` varchar(30) DEFAULT NULL,
  `bc_document` varchar(50) DEFAULT NULL,
  `bc_date` date DEFAULT NULL,
  `awb_no` varchar(50) DEFAULT NULL,
  `awb_date` date DEFAULT NULL,
  `qty_po` decimal(20,2) DEFAULT '0.00',
  `qty_delivery` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_receipt` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_balance` decimal(20,2) DEFAULT '0.00',
  `qty_mpq` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_label` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,5) DEFAULT NULL,
  `total` decimal(20,5) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `attachment` text,
  `status` tinyint(1) NOT NULL,
  `cancel_status` timestamp NULL DEFAULT NULL,
  `confirm` varchar(10) DEFAULT NULL,
  `confirm_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_receipt_others`
--

CREATE TABLE `purchase_order_receipt_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `asset_code` varchar(20) DEFAULT NULL,
  `asset_code_sub` varchar(20) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `bc_kind` varchar(30) DEFAULT NULL,
  `bc_aju` varchar(30) DEFAULT NULL,
  `bc_document` varchar(50) DEFAULT NULL,
  `bc_date` date DEFAULT NULL,
  `awb_no` varchar(50) DEFAULT NULL,
  `awb_date` date DEFAULT NULL,
  `qty_po` decimal(10,2) DEFAULT '0.00',
  `qty_delivery` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_receipt` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_mpq` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_label` int(11) NOT NULL DEFAULT '0',
  `location` varchar(30) DEFAULT NULL,
  `attachment` text,
  `store` tinyint(4) NOT NULL DEFAULT '0',
  `store_by` varchar(30) DEFAULT NULL,
  `store_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_receipt_qas`
--

CREATE TABLE `purchase_order_receipt_qas` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `label_no` varchar(30) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,5) DEFAULT '0.00000',
  `total` decimal(20,2) DEFAULT '0.00',
  `status` varchar(5) NOT NULL DEFAULT 'OK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_rfq`
--

CREATE TABLE `purchase_order_rfq` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `mpq` int(11) NOT NULL DEFAULT '0',
  `moq` int(11) NOT NULL DEFAULT '0',
  `leadtime` int(11) NOT NULL DEFAULT '0',
  `currency` varchar(30) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `remarks` text,
  `attachment_1` text,
  `attachment_2` text,
  `attachment_3` text,
  `revision` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_services`
--

CREATE TABLE `purchase_order_services` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_name` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `asset_code` varchar(30) DEFAULT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `hsn_code` varchar(30) DEFAULT NULL,
  `terms` text,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `price` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `total` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `taxes` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_dp` decimal(20,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `attachment` text,
  `revision` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `asset_code` varchar(20) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT '0',
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `request_type` varchar(10) NOT NULL DEFAULT 'RM',
  `expected_date` date DEFAULT NULL,
  `need` int(11) NOT NULL DEFAULT '0',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `forecast_m1` int(11) NOT NULL DEFAULT '0',
  `forecast_m2` int(11) NOT NULL DEFAULT '0',
  `forecast_m3` int(11) NOT NULL DEFAULT '0',
  `forecast_plus_1` int(11) NOT NULL DEFAULT '0',
  `forecast_plus_2` int(11) NOT NULL DEFAULT '0',
  `forecast_plus_3` int(11) NOT NULL DEFAULT '0',
  `forecast_avg` int(11) NOT NULL DEFAULT '0',
  `attachment` text,
  `remarks` text,
  `create_po` timestamp NULL DEFAULT NULL,
  `confirm` tinyint(4) NOT NULL DEFAULT '0',
  `confirm_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `status_data` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_request_others`
--

CREATE TABLE `purchase_request_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `asset_code` varchar(20) DEFAULT NULL,
  `asset_code_sub` varchar(30) DEFAULT NULL,
  `item_rm_number` varchar(30) DEFAULT NULL,
  `item_rm_name` varchar(50) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT '0',
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `request_type` varchar(10) NOT NULL DEFAULT 'RM',
  `expected_date` date DEFAULT NULL,
  `need` int(11) NOT NULL DEFAULT '0',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(5) NOT NULL DEFAULT 'INR',
  `remarks` text,
  `create_po` timestamp NULL DEFAULT NULL,
  `confirm` tinyint(4) NOT NULL DEFAULT '0',
  `confirm_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `status_data` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_request_rfq`
--

CREATE TABLE `purchase_request_rfq` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_rm_number` varchar(30) DEFAULT NULL,
  `item_rm_name` varchar(50) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT '0',
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `request_type` varchar(10) NOT NULL DEFAULT 'RM',
  `expected_date` date DEFAULT NULL,
  `need` int(11) NOT NULL DEFAULT '0',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(5) NOT NULL DEFAULT 'INR',
  `remarks` text,
  `create_po` timestamp NULL DEFAULT NULL,
  `confirm` tinyint(4) NOT NULL DEFAULT '0',
  `confirm_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `status_data` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_request_services`
--

CREATE TABLE `purchase_request_services` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT '0',
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `request_type` varchar(10) NOT NULL DEFAULT 'RM',
  `expected_date` date DEFAULT NULL,
  `need` int(11) NOT NULL DEFAULT '0',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(5) NOT NULL DEFAULT 'INR',
  `remarks` text,
  `create_po` timestamp NULL DEFAULT NULL,
  `confirm` tinyint(4) NOT NULL DEFAULT '0',
  `confirm_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `status_data` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `source` varchar(10) DEFAULT NULL,
  `return_no` varchar(30) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_name` varchar(50) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(11,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `qas`
--

CREATE TABLE `qas` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `status` varchar(5) NOT NULL DEFAULT 'OK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_for_quotations`
--

CREATE TABLE `request_for_quotations` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `rfq_no` varchar(30) DEFAULT NULL,
  `rfq_date` date DEFAULT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `price` decimal(10,4) DEFAULT NULL,
  `mpq` int(11) DEFAULT NULL,
  `moq` int(11) DEFAULT NULL,
  `leadtime` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `return_materials`
--

CREATE TABLE `return_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `return_no` varchar(30) DEFAULT NULL,
  `return_id` varchar(30) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_name` varchar(50) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `mpq` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,4) DEFAULT '0.0000',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `return_material_labels`
--

CREATE TABLE `return_material_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `return_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rgp_transactions`
--

CREATE TABLE `rgp_transactions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `rgp_id` varchar(50) DEFAULT NULL,
  `process_type` varchar(30) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `company_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `departement_id` varchar(30) DEFAULT NULL,
  `rgp_type_id` varchar(30) DEFAULT NULL,
  `reference_number` varchar(30) DEFAULT NULL,
  `return_date_estimation` date DEFAULT NULL,
  `rgp_number` varchar(30) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `notes` text,
  `item_id` varchar(30) DEFAULT NULL,
  `uom` varchar(30) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `hsn_code` varchar(30) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `igst_value` decimal(10,2) DEFAULT NULL,
  `sgst_value` decimal(10,2) DEFAULT NULL,
  `cgst_value` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `declaration` text,
  `status_si` tinyint(1) DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `print_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rgp_transaction_labels`
--

CREATE TABLE `rgp_transaction_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `rgp_number` varchar(50) DEFAULT NULL,
  `rgp_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rgp_types`
--

CREATE TABLE `rgp_types` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `safety_stock`
--

CREATE TABLE `safety_stock` (
  `id` varchar(30) NOT NULL DEFAULT '',
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `number` varchar(10) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `start` decimal(20,2) NOT NULL DEFAULT '0.00',
  `ending` decimal(20,2) NOT NULL DEFAULT '0.00',
  `safety` decimal(20,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'SALES',
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `taxes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_term` int(10) NOT NULL DEFAULT '0',
  `currency` varchar(10) DEFAULT NULL,
  `currency_local` varchar(10) DEFAULT NULL,
  `total_sub` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_discount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_vat` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_pph` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_grand` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `total_grand_local` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_local` decimal(20,2) NOT NULL DEFAULT '0.00',
  `vehicle_no` varchar(30) DEFAULT NULL,
  `remarks` text,
  `voucher` varchar(20) DEFAULT NULL,
  `so_number` varchar(50) DEFAULT NULL,
  `dn_number` varchar(50) DEFAULT NULL,
  `rtn_number` varchar(30) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `item_no` varchar(30) DEFAULT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `uom` varchar(20) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rate` decimal(20,2) NOT NULL DEFAULT '0.00',
  `igst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `sgst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `cgst` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total2` decimal(20,2) NOT NULL DEFAULT '0.00',
  `tcs` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total_freight` decimal(20,2) NOT NULL DEFAULT '0.00',
  `tcs_option` decimal(20,2) NOT NULL DEFAULT '0.00',
  `hsn_code` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `irn` text,
  `barcode` text,
  `ack_no` varchar(30) DEFAULT NULL,
  `ack_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `cancel` tinyint(1) DEFAULT '0',
  `cancel_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_invoice_journals`
--

CREATE TABLE `sales_invoice_journals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `debit` decimal(20,2) DEFAULT '0.00',
  `credit` decimal(20,2) DEFAULT '0.00',
  `flag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `customer_address_id` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `item_fg_id` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `customer_order_no` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `forecast_no` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `revision` varchar(2) CHARACTER SET utf8mb4 NOT NULL,
  `sales_order_no` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `sales_order_date` date DEFAULT NULL,
  `division` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4,
  `attachment` text CHARACTER SET utf8mb4,
  `closing_reason` text CHARACTER SET utf8mb4,
  `closing_date` date DEFAULT NULL,
  `uom` varchar(20) CHARACTER SET utf8mb4 DEFAULT NULL,
  `pph` int(11) NOT NULL DEFAULT '0',
  `taxes` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) DEFAULT '0',
  `delivery` int(11) NOT NULL DEFAULT '0',
  `outstanding` int(11) NOT NULL DEFAULT '0',
  `currency` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `total` decimal(20,4) DEFAULT '0.0000',
  `total_sub` decimal(20,4) DEFAULT '0.0000',
  `total_tax` decimal(20,2) DEFAULT '0.00',
  `total_pph` int(11) DEFAULT '0',
  `total_grand` decimal(20,4) DEFAULT '0.0000',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `plant` varchar(30) CHARACTER SET utf8mb4 DEFAULT NULL,
  `department` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
  `attention_to` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_order_deliveries`
--

CREATE TABLE `sales_order_deliveries` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `plant` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `customer_address_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_fg_number` varchar(30) DEFAULT NULL,
  `item_fg_customer` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `sales_invoice_no` varchar(30) DEFAULT NULL,
  `delivery_note_no` varchar(30) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_no` varchar(30) DEFAULT NULL,
  `return_id` varchar(30) DEFAULT NULL,
  `uom` varchar(30) DEFAULT NULL,
  `hsn_code` varchar(30) DEFAULT NULL,
  `qty_invoice` decimal(10,2) DEFAULT '0.00',
  `qty_return` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_lot` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_box` int(11) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL,
  `cancel_status` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_return_labels`
--

CREATE TABLE `sales_return_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `return_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sample_requests`
--

CREATE TABLE `sample_requests` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_receipts`
--

CREATE TABLE `scan_item_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,5) DEFAULT '0.00000',
  `label_from` varchar(30) DEFAULT NULL,
  `warehouse` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_receipts_fg`
--

CREATE TABLE `scan_item_receipts_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) NOT NULL,
  `checksheet_label` varchar(30) NOT NULL,
  `so_number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `warehouse` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_rejections`
--

CREATE TABLE `scan_item_rejections` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,5) DEFAULT '0.00000',
  `warehouse` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_rgp`
--

CREATE TABLE `scan_item_rgp` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `rgp_number` varchar(50) DEFAULT NULL,
  `rgp_id` varchar(50) DEFAULT NULL,
  `item_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `price` decimal(20,5) DEFAULT '0.00000',
  `type` varchar(3) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scraps`
--

CREATE TABLE `scraps` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'IN',
  `qty` decimal(11,2) NOT NULL DEFAULT '0.00',
  `ng` int(11) NOT NULL DEFAULT '0',
  `source` varchar(30) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scrap_booking`
--

CREATE TABLE `scrap_booking` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_menus`
--

CREATE TABLE `setting_menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `menus_id` varchar(30) DEFAULT NULL,
  `m_view` varchar(5) DEFAULT NULL,
  `m_add` varchar(5) DEFAULT NULL,
  `m_edit` varchar(5) DEFAULT NULL,
  `m_delete` varchar(5) DEFAULT NULL,
  `m_upload` varchar(5) DEFAULT NULL,
  `m_download` varchar(5) DEFAULT NULL,
  `m_print` varchar(5) DEFAULT NULL,
  `m_excel` varchar(5) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_prodfam`
--

CREATE TABLE `setting_prodfam` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_users`
--

CREATE TABLE `setting_users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `users_id` varchar(30) DEFAULT NULL,
  `menus_id` varchar(30) DEFAULT NULL,
  `v_view` tinyint(1) NOT NULL DEFAULT '0',
  `v_add` tinyint(1) NOT NULL DEFAULT '0',
  `v_edit` tinyint(1) NOT NULL DEFAULT '0',
  `v_delete` tinyint(1) NOT NULL DEFAULT '0',
  `v_upload` tinyint(1) NOT NULL DEFAULT '0',
  `v_download` tinyint(1) NOT NULL DEFAULT '0',
  `v_print` tinyint(1) NOT NULL DEFAULT '0',
  `v_excel` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_orders`
--

CREATE TABLE `shipping_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `delivery_order_no` varchar(30) NOT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `delivery` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `signatures`
--

CREATE TABLE `signatures` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_prepared` varchar(30) NOT NULL,
  `po_checked` varchar(30) NOT NULL,
  `po_approved` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `states`
--

CREATE TABLE `states` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `country_id` varchar(20) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `irn_code` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_rm`
--

CREATE TABLE `stock_rm` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `cutoff` date DEFAULT NULL,
  `item_rm_id` varchar(100) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT '0.00',
  `actual` decimal(10,2) DEFAULT '0.00',
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_wip`
--

CREATE TABLE `stock_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) NOT NULL,
  `p_year` varchar(4) NOT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT '0',
  `document_no` varchar(30) DEFAULT NULL,
  `pp` decimal(20,2) NOT NULL DEFAULT '0.00',
  `wc` int(11) NOT NULL DEFAULT '0',
  `ha` int(11) NOT NULL DEFAULT '0',
  `c` int(11) NOT NULL DEFAULT '0',
  `v` int(11) NOT NULL DEFAULT '0',
  `p` int(11) NOT NULL DEFAULT '0',
  `rfg` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_fg`
--

CREATE TABLE `sto_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `remarks` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_ot`
--

CREATE TABLE `sto_ot` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `price` decimal(20,4) DEFAULT '0.0000',
  `currency` varchar(4) DEFAULT 'INR',
  `location_other_number` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_rm`
--

CREATE TABLE `sto_rm` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `price` decimal(20,4) DEFAULT '0.0000',
  `currency` varchar(3) DEFAULT 'INR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_store`
--

CREATE TABLE `sto_store` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` int(11) DEFAULT '0',
  `location_other_number` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_wip`
--

CREATE TABLE `sto_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `document_no` varchar(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `qty` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) DEFAULT NULL,
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `supplier_type` varchar(30) DEFAULT NULL,
  `supplier_po` varchar(30) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `origin` varchar(30) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `address` text,
  `contact_person` varchar(30) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `payment_term` int(11) DEFAULT '0',
  `tds` varchar(30) DEFAULT NULL,
  `incoterm` varchar(20) DEFAULT NULL,
  `vat` int(11) NOT NULL DEFAULT '0',
  `vat_status` varchar(10) DEFAULT NULL,
  `tax` int(11) NOT NULL DEFAULT '0',
  `gst` varchar(30) DEFAULT NULL,
  `pen` varchar(30) DEFAULT NULL,
  `ifsc` varchar(30) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `commodity` varchar(10) NOT NULL DEFAULT 'YES',
  `idn_no` varchar(30) DEFAULT NULL,
  `msmi` varchar(10) DEFAULT NULL,
  `terms` text,
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_items`
--

CREATE TABLE `supplier_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `maker_id` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `item_supplier` varchar(30) DEFAULT NULL,
  `mpq` int(11) DEFAULT '0',
  `moq` int(11) DEFAULT '0',
  `share_order` int(11) DEFAULT '0',
  `leadtime` int(11) DEFAULT '0',
  `transit` int(11) NOT NULL DEFAULT '0',
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `valid_date` date DEFAULT NULL,
  `safety_stock` int(11) DEFAULT '0',
  `calculate` varchar(10) DEFAULT NULL,
  `remarks` text,
  `remarks1` text NOT NULL,
  `remarks2` text NOT NULL,
  `remarks3` text NOT NULL,
  `remarks4` text NOT NULL,
  `attachment` text,
  `attachment2` text,
  `attachment3` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_item_histories`
--

CREATE TABLE `supplier_item_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `maker_id` varchar(30) DEFAULT NULL,
  `item_supplier` varchar(30) DEFAULT NULL,
  `mpq` int(11) DEFAULT '0',
  `moq` int(11) DEFAULT '0',
  `share_order` int(11) DEFAULT '0',
  `leadtime` int(11) DEFAULT '0',
  `transit` int(11) NOT NULL DEFAULT '0',
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `valid_date` date DEFAULT NULL,
  `safety_stock` int(11) DEFAULT '0',
  `calculate` varchar(10) DEFAULT NULL,
  `remarks` text,
  `attachment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_item_prices`
--

CREATE TABLE `supplier_item_prices` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `valid_date` date DEFAULT NULL,
  `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_item_price_histories`
--

CREATE TABLE `supplier_item_price_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_item_price_id` varchar(30) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT '0.0000',
  `start_date` date DEFAULT NULL,
  `valid_date` date DEFAULT NULL,
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_materials`
--

CREATE TABLE `supply_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `period` varchar(10) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_requestions`
--

CREATE TABLE `supply_requestions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `rejection_no` varchar(30) DEFAULT NULL,
  `scrap_no` varchar(30) DEFAULT NULL,
  `period` varchar(10) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL,
  `description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_sheets`
--

CREATE TABLE `supply_sheets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `mpq` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty_begin` decimal(20,2) DEFAULT '0.00',
  `qty_wo` decimal(20,2) DEFAULT '0.00',
  `qty_req` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_act` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_issued` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_bal` decimal(20,2) NOT NULL DEFAULT '0.00',
  `type` varchar(10) DEFAULT NULL,
  `remarks` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `taxes`
--

CREATE TABLE `taxes` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `cgst` decimal(20,2) DEFAULT '0.00',
  `sgst` decimal(20,2) DEFAULT '0.00',
  `igst` decimal(20,2) DEFAULT '0.00',
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(30) NOT NULL,
  `account_number_1` varchar(30) DEFAULT NULL,
  `account_name_1` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tds`
--

CREATE TABLE `tds` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `section` varchar(10) DEFAULT NULL,
  `payment` varchar(200) DEFAULT NULL,
  `exceed` decimal(20,2) NOT NULL DEFAULT '0.00',
  `individual` decimal(20,2) NOT NULL DEFAULT '0.00',
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `terminals`
--

CREATE TABLE `terminals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `maker_id` varchar(30) NOT NULL,
  `chcb` decimal(20,2) DEFAULT '0.00',
  `cwcb` decimal(20,2) DEFAULT '0.00',
  `cwib` decimal(20,2) DEFAULT '0.00',
  `chvb` decimal(20,2) DEFAULT '0.00',
  `chvbs` varchar(50) DEFAULT NULL,
  `stripping` varchar(20) NOT NULL,
  `wire_type` varchar(20) NOT NULL,
  `wire_size` varchar(20) NOT NULL,
  `pull_force` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `trial_balances`
--

CREATE TABLE `trial_balances` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `period` varchar(10) NOT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `begin_debit` decimal(20,2) DEFAULT '0.00',
  `begin_credit` decimal(20,2) DEFAULT '0.00',
  `local_debit` decimal(20,2) DEFAULT '0.00',
  `local_credit` decimal(20,2) DEFAULT '0.00',
  `ending_debit` decimal(20,2) DEFAULT '0.00',
  `ending_credit` decimal(20,2) DEFAULT '0.00',
  `header` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `umh`
--

CREATE TABLE `umh` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `item_fg_id` varchar(30) DEFAULT NULL,
  `circuit_no` varchar(10) DEFAULT NULL,
  `process_main_number` varchar(30) DEFAULT NULL,
  `process_sub_number` varchar(30) DEFAULT NULL,
  `process_number` varchar(30) DEFAULT NULL,
  `cycle_time` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `remarks` text,
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `uom`
--

CREATE TABLE `uom` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `approved_data` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `avatar` text,
  `theme` varchar(30) NOT NULL DEFAULT 'default',
  `actived` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `vehicles`
--

CREATE TABLE `vehicles` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `police_no` varchar(10) DEFAULT NULL,
  `dimension_p` int(11) NOT NULL DEFAULT '0',
  `dimension_l` int(11) NOT NULL DEFAULT '0',
  `dimension_t` int(11) NOT NULL DEFAULT '0',
  `volume` int(11) DEFAULT '0',
  `remark` text NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_locations`
--

CREATE TABLE `warehouse_locations` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `type` varchar(10) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `location` varchar(30) NOT NULL,
  `area` varchar(30) NOT NULL,
  `rack` varchar(30) NOT NULL,
  `level` varchar(30) NOT NULL,
  `level_sub` varchar(30) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_location_items`
--

CREATE TABLE `warehouse_location_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '',
  `item_rm_id` varchar(30) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `warehouse_location_id` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_location_others`
--

CREATE TABLE `warehouse_location_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_balances`
--

CREATE TABLE `wip_balances` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `begin` decimal(10,2) NOT NULL DEFAULT '0.00',
  `need` decimal(10,2) NOT NULL DEFAULT '0.00',
  `receipt` decimal(20,2) NOT NULL DEFAULT '0.00',
  `issued` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `warehouse` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipts`
--

CREATE TABLE `wip_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `trans_date_from` date DEFAULT NULL,
  `trans_date_to` date DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `lot_label` int(11) NOT NULL DEFAULT '0',
  `lot_box` int(11) NOT NULL DEFAULT '0',
  `label` int(11) NOT NULL DEFAULT '0',
  `label_box` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_balance`
--

CREATE TABLE `wip_receipt_balance` (
  `checksheet_number` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `qpa` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `rfg` decimal(20,2) NOT NULL DEFAULT '0.00',
  `consumed` decimal(20,2) NOT NULL DEFAULT '0.00',
  `issued` decimal(20,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_boxs`
--

CREATE TABLE `wip_receipt_boxs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_details`
--

CREATE TABLE `wip_receipt_details` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qpa` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rfg` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_labels`
--

CREATE TABLE `wip_receipt_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_reports`
--

CREATE TABLE `wip_reports` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `qty` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wos`
--

CREATE TABLE `wos` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `circuit_no` varchar(50) DEFAULT '0',
  `kind` varchar(30) DEFAULT NULL,
  `size` varchar(30) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `line_color` varchar(30) DEFAULT NULL,
  `tube_size_1` varchar(30) DEFAULT NULL,
  `tube_size_2` varchar(30) DEFAULT NULL,
  `cl` varchar(30) DEFAULT NULL,
  `jno` varchar(30) DEFAULT NULL,
  `twist` varchar(30) DEFAULT NULL,
  `mno` varchar(30) DEFAULT NULL,
  `dc_1` varchar(30) DEFAULT NULL,
  `dc_2` varchar(30) DEFAULT NULL,
  `terminal_1` varchar(30) DEFAULT NULL,
  `terminal_2` varchar(30) DEFAULT NULL,
  `strip_1` varchar(30) DEFAULT NULL,
  `strip_2` varchar(30) DEFAULT NULL,
  `chc_1` varchar(30) DEFAULT NULL,
  `chc_2` varchar(30) DEFAULT NULL,
  `cwc_1` varchar(30) DEFAULT NULL,
  `cwc_2` varchar(30) DEFAULT NULL,
  `chi_1` varchar(30) DEFAULT NULL,
  `chi_2` varchar(30) DEFAULT NULL,
  `cwi_1` varchar(30) DEFAULT NULL,
  `cwi_2` varchar(30) DEFAULT NULL,
  `accessories_1` varchar(30) DEFAULT NULL,
  `accessories_2` varchar(30) DEFAULT NULL,
  `mark_r` varchar(30) DEFAULT NULL,
  `mark_f` varchar(30) DEFAULT NULL,
  `tube_1` varchar(30) DEFAULT NULL,
  `tube_2` varchar(30) DEFAULT NULL,
  `end_1` varchar(30) DEFAULT NULL,
  `end_2` varchar(30) DEFAULT NULL,
  `note_r` varchar(100) DEFAULT NULL,
  `note_f` varchar(100) DEFAULT NULL,
  `cct_order` varchar(30) DEFAULT NULL,
  `cct_range` varchar(30) DEFAULT NULL,
  `mc` varchar(30) DEFAULT NULL,
  `order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `account_balance_customers`
--
ALTER TABLE `account_balance_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_balance_suppliers`
--
ALTER TABLE `account_balance_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_banks`
--
ALTER TABLE `account_banks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_cash_flow`
--
ALTER TABLE `account_cash_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_coa`
--
ALTER TABLE `account_coa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `account_group_detail_id` (`account_group_detail_id`);

--
-- Indeks untuk tabel `account_groups`
--
ALTER TABLE `account_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_group_details`
--
ALTER TABLE `account_group_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `account_group_id` (`account_group_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_imprests`
--
ALTER TABLE `account_imprests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_lock`
--
ALTER TABLE `account_lock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `period` (`period`);

--
-- Indeks untuk tabel `account_period`
--
ALTER TABLE `account_period`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `period` (`period`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_statements`
--
ALTER TABLE `account_statements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `ap_payments`
--
ALTER TABLE `ap_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `payment_no` (`payment_no`);

--
-- Indeks untuk tabel `ap_payment_journals`
--
ALTER TABLE `ap_payment_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `payment_no` (`payment_no`);

--
-- Indeks untuk tabel `ar_receipts`
--
ALTER TABLE `ar_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `receipt_no` (`receipt_no`);

--
-- Indeks untuk tabel `ar_receipt_journals`
--
ALTER TABLE `ar_receipt_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `asset_disposals`
--
ALTER TABLE `asset_disposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `asset_fixed_id` (`asset_fixed_id`);

--
-- Indeks untuk tabel `asset_fixeds`
--
ALTER TABLE `asset_fixeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `asset_journals`
--
ALTER TABLE `asset_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `asset_journal_details`
--
ALTER TABLE `asset_journal_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `balance_sheets`
--
ALTER TABLE `balance_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `balance_wip`
--
ALTER TABLE `balance_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `barcode_divides`
--
ALTER TABLE `barcode_divides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `reff` (`reff`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `barcode_divides_fg`
--
ALTER TABLE `barcode_divides_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `bc_kind`
--
ALTER TABLE `bc_kind`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `begin_wip`
--
ALTER TABLE `begin_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `bom_log`
--
ALTER TABLE `bom_log`
  ADD KEY `created_by` (`created_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `from_users_id` (`from_users_id`),
  ADD KEY `to_users_id` (`to_users_id`);

--
-- Indeks untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `country_id` (`country_id`),
  ADD KEY `cities_ibfk_4` (`state_id`);

--
-- Indeks untuk tabel `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `convertions`
--
ALTER TABLE `convertions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `costing_components`
--
ALTER TABLE `costing_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `costing_products`
--
ALTER TABLE `costing_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `costing_product_details`
--
ALTER TABLE `costing_product_details`
  ADD KEY `item_fg_id` (`item_rm_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `costing_product_workorders`
--
ALTER TABLE `costing_product_workorders`
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_address_ibfk_3` (`customer_id`);

--
-- Indeks untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `customer_item_histories`
--
ALTER TABLE `customer_item_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`),
  ADD KEY `delivery_order_no` (`delivery_order_no`),
  ADD KEY `delivery_note_no` (`delivery_note_no`);

--
-- Indeks untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`),
  ADD KEY `delivery_order_no` (`delivery_order_no`);

--
-- Indeks untuk tabel `delivery_schedules`
--
ALTER TABLE `delivery_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `number` (`number`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE;

--
-- Indeks untuk tabel `dol_items`
--
ALTER TABLE `dol_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `ecn`
--
ALTER TABLE `ecn`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `ecn_comment`
--
ALTER TABLE `ecn_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `forecasts`
--
ALTER TABLE `forecasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `forecast_histories`
--
ALTER TABLE `forecast_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `generate_item_calculation`
--
ALTER TABLE `generate_item_calculation`
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_loadcap`
--
ALTER TABLE `generate_loadcap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_loadcap_line`
--
ALTER TABLE `generate_loadcap_line`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_loadcap_product`
--
ALTER TABLE `generate_loadcap_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_machine_capacity`
--
ALTER TABLE `generate_machine_capacity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_mpp`
--
ALTER TABLE `generate_mpp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `generate_mps`
--
ALTER TABLE `generate_mps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `generate_mps_details`
--
ALTER TABLE `generate_mps_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `generate_mrp`
--
ALTER TABLE `generate_mrp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`) USING BTREE;

--
-- Indeks untuk tabel `generate_mrp_abcclass`
--
ALTER TABLE `generate_mrp_abcclass`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `generate_mrp_abcclass_ibfk_3` (`item_rm_id`);

--
-- Indeks untuk tabel `generate_mrp_finals`
--
ALTER TABLE `generate_mrp_finals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `generate_mrp_finals_ibfk_3` (`item_rm_id`);

--
-- Indeks untuk tabel `harnes_types`
--
ALTER TABLE `harnes_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `hsn`
--
ALTER TABLE `hsn`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `income_statements`
--
ALTER TABLE `income_statements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `incoterms`
--
ALTER TABLE `incoterms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `inventory_fg`
--
ALTER TABLE `inventory_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `inventory_rm`
--
ALTER TABLE `inventory_rm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `item_family_id` (`item_family_id`);

--
-- Indeks untuk tabel `inventory_wip`
--
ALTER TABLE `inventory_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `issued_materials`
--
ALTER TABLE `issued_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `issued_material_details`
--
ALTER TABLE `issued_material_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `issued_others`
--
ALTER TABLE `issued_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `issued_others_request`
--
ALTER TABLE `issued_others_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `issued_others_store`
--
ALTER TABLE `issued_others_store`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `item_rm_id` (`item_rm_id`) USING BTREE,
  ADD KEY `request_no` (`request_no`) USING BTREE;

--
-- Indeks untuk tabel `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_colors`
--
ALTER TABLE `item_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_familys`
--
ALTER TABLE `item_familys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_number` (`item_category_id`);

--
-- Indeks untuk tabel `item_family_subs`
--
ALTER TABLE `item_family_subs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_id` (`item_category_id`),
  ADD KEY `item_family_id` (`item_family_id`);

--
-- Indeks untuk tabel `item_fg`
--
ALTER TABLE `item_fg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_kinds`
--
ALTER TABLE `item_kinds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_mpq`
--
ALTER TABLE `item_mpq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_ng`
--
ALTER TABLE `item_ng`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `workorder` (`workorder`);

--
-- Indeks untuk tabel `item_platting`
--
ALTER TABLE `item_platting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_rm`
--
ALTER TABLE `item_rm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_id` (`item_category_id`),
  ADD KEY `item_family_id` (`item_family_id`),
  ADD KEY `item_sub_family_id` (`item_sub_family_id`);

--
-- Indeks untuk tabel `item_sizes`
--
ALTER TABLE `item_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `journal_clearings`
--
ALTER TABLE `journal_clearings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `journal_postings`
--
ALTER TABLE `journal_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `journal_revaluations`
--
ALTER TABLE `journal_revaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `journal_setups`
--
ALTER TABLE `journal_setups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `journal_type_id` (`journal_type_id`);

--
-- Indeks untuk tabel `journal_types`
--
ALTER TABLE `journal_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `line_productions`
--
ALTER TABLE `line_productions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `makers`
--
ALTER TABLE `makers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `menus_no`
--
ALTER TABLE `menus_no`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `new_barcode`
--
ALTER TABLE `new_barcode`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `label_no` (`label_no`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `new_barcode_fg`
--
ALTER TABLE `new_barcode_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `label_no` (`label_no`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `ng_types`
--
ALTER TABLE `ng_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `origins`
--
ALTER TABLE `origins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `os_fg`
--
ALTER TABLE `os_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `os_po`
--
ALTER TABLE `os_po`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `os_rm`
--
ALTER TABLE `os_rm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `os_rm_labels`
--
ALTER TABLE `os_rm_labels`
  ADD PRIMARY KEY (`id`,`label_no`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `os_supply`
--
ALTER TABLE `os_supply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `os_wo`
--
ALTER TABLE `os_wo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `dn_number` (`dn_number`);

--
-- Indeks untuk tabel `payment_terms`
--
ALTER TABLE `payment_terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `process`
--
ALTER TABLE `process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `process_capacity`
--
ALTER TABLE `process_capacity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `process_main_number` (`process_main_number`),
  ADD KEY `process_sub_number` (`process_sub_number`),
  ADD KEY `process_number` (`process_number`);

--
-- Indeks untuk tabel `process_main`
--
ALTER TABLE `process_main`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `process_main_sub`
--
ALTER TABLE `process_main_sub`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `process_main_id` (`process_main_id`);

--
-- Indeks untuk tabel `production_schedules`
--
ALTER TABLE `production_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `workorder` (`workorder`);

--
-- Indeks untuk tabel `purchase_confirm`
--
ALTER TABLE `purchase_confirm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `purchase_confirm_ibfk_3` (`po_no`),
  ADD KEY `purchase_confirm_ibfk_4` (`item_rm_id`);

--
-- Indeks untuk tabel `purchase_confirm_others`
--
ALTER TABLE `purchase_confirm_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `purchase_confirm_others_ibfk_3` (`po_no`),
  ADD KEY `purchase_confirm_others_ibfk_4` (`item_rm_id`);

--
-- Indeks untuk tabel `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `purchase_invoices_ibfk_5` (`journal_type_id`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `purchase_invoice_journals`
--
ALTER TABLE `purchase_invoice_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `request_no` (`request_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_labels`
--
ALTER TABLE `purchase_order_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `receipt_no` (`receipt_id`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `purchase_order_others`
--
ALTER TABLE `purchase_order_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_receipts`
--
ALTER TABLE `purchase_order_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `receipt_no` (`receipt_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_receipt_others`
--
ALTER TABLE `purchase_order_receipt_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `receipt_no` (`receipt_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_receipt_qas`
--
ALTER TABLE `purchase_order_receipt_qas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `receipt_no` (`receipt_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_rfq`
--
ALTER TABLE `purchase_order_rfq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_services`
--
ALTER TABLE `purchase_order_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `purchase_request_others`
--
ALTER TABLE `purchase_request_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `purchase_request_rfq`
--
ALTER TABLE `purchase_request_rfq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `purchase_request_services`
--
ALTER TABLE `purchase_request_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `qas`
--
ALTER TABLE `qas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `receipt_no` (`receipt_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `request_for_quotations`
--
ALTER TABLE `request_for_quotations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `item_rm_id` (`item_rm_id`) USING BTREE,
  ADD KEY `request_no` (`rfq_no`) USING BTREE,
  ADD KEY `request_for_quotations_ibfk_3` (`supplier_id`) USING BTREE;

--
-- Indeks untuk tabel `return_materials`
--
ALTER TABLE `return_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `return_no` (`return_no`),
  ADD KEY `return_id` (`return_id`);

--
-- Indeks untuk tabel `return_material_labels`
--
ALTER TABLE `return_material_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `rgp_transactions`
--
ALTER TABLE `rgp_transactions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `departement_id` (`departement_id`),
  ADD KEY `rgp_type_id` (`rgp_type_id`),
  ADD KEY `currency` (`currency`),
  ADD KEY `number` (`number`) USING BTREE,
  ADD KEY `rgp_number` (`rgp_number`);

--
-- Indeks untuk tabel `rgp_transaction_labels`
--
ALTER TABLE `rgp_transaction_labels`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `label_no` (`label_no`) USING BTREE,
  ADD KEY `rgp_number` (`rgp_number`) USING BTREE;

--
-- Indeks untuk tabel `rgp_types`
--
ALTER TABLE `rgp_types`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `number` (`number`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE;

--
-- Indeks untuk tabel `safety_stock`
--
ALTER TABLE `safety_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `number` (`number`),
  ADD KEY `journal_type_id` (`journal_type_id`);

--
-- Indeks untuk tabel `sales_invoice_journals`
--
ALTER TABLE `sales_invoice_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `sales_orders_ibfk_3` (`customer_id`),
  ADD KEY `sales_orders_ibfk_4` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`);

--
-- Indeks untuk tabel `sales_order_deliveries`
--
ALTER TABLE `sales_order_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE;

--
-- Indeks untuk tabel `sales_return_labels`
--
ALTER TABLE `sales_return_labels`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `label_no` (`label_no`) USING BTREE;

--
-- Indeks untuk tabel `sample_requests`
--
ALTER TABLE `sample_requests`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `item_rm_id` (`item_rm_id`) USING BTREE;

--
-- Indeks untuk tabel `scan_item_receipts`
--
ALTER TABLE `scan_item_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `scan_item_receipts_fg`
--
ALTER TABLE `scan_item_receipts_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `scan_item_rejections`
--
ALTER TABLE `scan_item_rejections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `scan_item_rgp`
--
ALTER TABLE `scan_item_rgp`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE;

--
-- Indeks untuk tabel `scraps`
--
ALTER TABLE `scraps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `scrap_booking`
--
ALTER TABLE `scrap_booking`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE,
  ADD KEY `material_scraps_ibfk_3` (`item_id`) USING BTREE;

--
-- Indeks untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `setting_prodfam`
--
ALTER TABLE `setting_prodfam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `setting_prodfam_types_ibfk_3` (`item_category_id`),
  ADD KEY `setting_prodfam_types_ibfk_4` (`item_family_id`);

--
-- Indeks untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `shipping_orders`
--
ALTER TABLE `shipping_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_order_no` (`delivery_order_no`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `signatures`
--
ALTER TABLE `signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `states_ibfk_3` (`country_id`);

--
-- Indeks untuk tabel `stock_rm`
--
ALTER TABLE `stock_rm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `stock_wip`
--
ALTER TABLE `stock_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `sto_fg`
--
ALTER TABLE `sto_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `sto_ot`
--
ALTER TABLE `sto_ot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `location_other_number` (`location_other_number`);

--
-- Indeks untuk tabel `sto_rm`
--
ALTER TABLE `sto_rm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `sto_store`
--
ALTER TABLE `sto_store`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `location_other_number` (`location_other_number`);

--
-- Indeks untuk tabel `sto_wip`
--
ALTER TABLE `sto_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supplier_item_histories`
--
ALTER TABLE `supplier_item_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supplier_item_prices`
--
ALTER TABLE `supplier_item_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supplier_item_price_histories`
--
ALTER TABLE `supplier_item_price_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_item_price_id` (`supplier_item_price_id`) USING BTREE;

--
-- Indeks untuk tabel `supply_materials`
--
ALTER TABLE `supply_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supply_requestions`
--
ALTER TABLE `supply_requestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supply_sheets`
--
ALTER TABLE `supply_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `workorder` (`workorder`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `tds`
--
ALTER TABLE `tds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tds_ibfk_1` (`updated_by`),
  ADD KEY `tds_ibfk_2` (`created_by`);

--
-- Indeks untuk tabel `terminals`
--
ALTER TABLE `terminals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `trial_balances`
--
ALTER TABLE `trial_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `umh`
--
ALTER TABLE `umh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `process_main_number` (`process_main_number`),
  ADD KEY `process_sub_number` (`process_sub_number`);

--
-- Indeks untuk tabel `uom`
--
ALTER TABLE `uom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `warehouse_location_items`
--
ALTER TABLE `warehouse_location_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `warehouse_location_id` (`warehouse_location_id`);

--
-- Indeks untuk tabel `warehouse_location_others`
--
ALTER TABLE `warehouse_location_others`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `number` (`number`) USING BTREE,
  ADD KEY `created_by` (`created_by`) USING BTREE,
  ADD KEY `updated_by` (`updated_by`) USING BTREE;

--
-- Indeks untuk tabel `wip_balances`
--
ALTER TABLE `wip_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`) USING BTREE;

--
-- Indeks untuk tabel `wip_receipts`
--
ALTER TABLE `wip_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`,`item_fg_id`),
  ADD KEY `wip_receipts_ibfk_4` (`item_fg_id`);

--
-- Indeks untuk tabel `wip_receipt_boxs`
--
ALTER TABLE `wip_receipt_boxs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wip_receipt_details`
--
ALTER TABLE `wip_receipt_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`,`item_fg_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `wip_receipt_labels`
--
ALTER TABLE `wip_receipt_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wip_reports`
--
ALTER TABLE `wip_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wos`
--
ALTER TABLE `wos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ecn`
--
ALTER TABLE `ecn`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inventory_rm`
--
ALTER TABLE `inventory_rm`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logins`
--
ALTER TABLE `logins`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `wip_receipt_details`
--
ALTER TABLE `wip_receipt_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `wip_reports`
--
ALTER TABLE `wip_reports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `account_balance_customers`
--
ALTER TABLE `account_balance_customers`
  ADD CONSTRAINT `account_balance_customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_customers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_balance_suppliers`
--
ALTER TABLE `account_balance_suppliers`
  ADD CONSTRAINT `account_balance_suppliers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_suppliers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_suppliers_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_banks`
--
ALTER TABLE `account_banks`
  ADD CONSTRAINT `account_banks_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_banks_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_cash_flow`
--
ALTER TABLE `account_cash_flow`
  ADD CONSTRAINT `account_cash_flow_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_cash_flow_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_coa`
--
ALTER TABLE `account_coa`
  ADD CONSTRAINT `account_coa_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_coa_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_detail_id` FOREIGN KEY (`account_group_detail_id`) REFERENCES `account_group_details` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_groups`
--
ALTER TABLE `account_groups`
  ADD CONSTRAINT `account_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_groups_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_group_details`
--
ALTER TABLE `account_group_details`
  ADD CONSTRAINT `account_group_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_details_ibfk_3` FOREIGN KEY (`account_group_id`) REFERENCES `account_groups` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_imprests`
--
ALTER TABLE `account_imprests`
  ADD CONSTRAINT `account_imprests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_imprests_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_lock`
--
ALTER TABLE `account_lock`
  ADD CONSTRAINT `account_lock_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_lock_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_lock_ibfk_3` FOREIGN KEY (`period`) REFERENCES `account_period` (`period`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_period`
--
ALTER TABLE `account_period`
  ADD CONSTRAINT `account_period_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_period_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_statements`
--
ALTER TABLE `account_statements`
  ADD CONSTRAINT `account_statements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_statements_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ap_payments`
--
ALTER TABLE `ap_payments`
  ADD CONSTRAINT `ap_payments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_payments_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ap_payment_journals`
--
ALTER TABLE `ap_payment_journals`
  ADD CONSTRAINT `ap_payment_journals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_payment_journals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_payment_journals_ibfk_3` FOREIGN KEY (`payment_no`) REFERENCES `ap_payments` (`payment_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ar_receipts`
--
ALTER TABLE `ar_receipts`
  ADD CONSTRAINT `ar_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ar_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ar_receipts_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ar_receipt_journals`
--
ALTER TABLE `ar_receipt_journals`
  ADD CONSTRAINT `ar_receipt_journals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ar_receipt_journals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD CONSTRAINT `asset_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_categories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_disposals`
--
ALTER TABLE `asset_disposals`
  ADD CONSTRAINT `asset_disposals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_disposals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_fixeds`
--
ALTER TABLE `asset_fixeds`
  ADD CONSTRAINT `asset_fixeds_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_fixeds_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_journals`
--
ALTER TABLE `asset_journals`
  ADD CONSTRAINT `asset_journals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_journals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_journal_details`
--
ALTER TABLE `asset_journal_details`
  ADD CONSTRAINT `asset_journal_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_journal_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `balance_sheets`
--
ALTER TABLE `balance_sheets`
  ADD CONSTRAINT `balance_sheets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `balance_sheets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `balance_wip`
--
ALTER TABLE `balance_wip`
  ADD CONSTRAINT `balance_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `balance_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `barcode_divides`
--
ALTER TABLE `barcode_divides`
  ADD CONSTRAINT `barcode_divides_ibafk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `barcode_divides_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `barcode_divides_fg`
--
ALTER TABLE `barcode_divides_fg`
  ADD CONSTRAINT `barcode_divides_fg_ibafk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `barcode_divides_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bc_kind`
--
ALTER TABLE `bc_kind`
  ADD CONSTRAINT `bc_kind_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bc_kind_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `begin_wip`
--
ALTER TABLE `begin_wip`
  ADD CONSTRAINT `begin_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `begin_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `begin_wip_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `bom_log`
--
ALTER TABLE `bom_log`
  ADD CONSTRAINT `bom_log_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_log_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `bom_log_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `calendars`
--
ALTER TABLE `calendars`
  ADD CONSTRAINT `calendars_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calendars_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`from_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_4` FOREIGN KEY (`to_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD CONSTRAINT `checksheets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `checksheets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cities_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cities_ibfk_3` FOREIGN KEY (`country_id`) REFERENCES `origins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cities_ibfk_4` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`);

--
-- Ketidakleluasaan untuk tabel `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `config_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `convertions`
--
ALTER TABLE `convertions`
  ADD CONSTRAINT `convertions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convertions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convertions_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `costing_components`
--
ALTER TABLE `costing_components`
  ADD CONSTRAINT `costing_components_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_components_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `costing_products`
--
ALTER TABLE `costing_products`
  ADD CONSTRAINT `costing_products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_products_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_products_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `costing_product_details`
--
ALTER TABLE `costing_product_details`
  ADD CONSTRAINT `costing_product_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_product_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_product_details_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `costing_product_workorders`
--
ALTER TABLE `costing_product_workorders`
  ADD CONSTRAINT `costing_product_workorders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_product_workorders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `costing_product_workorders_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD CONSTRAINT `currencies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `currencies_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `customer_address_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_address_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_address_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD CONSTRAINT `customer_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `customer_items_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `customer_item_histories`
--
ALTER TABLE `customer_item_histories`
  ADD CONSTRAINT `customer_item_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_item_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_item_histories_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `customer_item_histories_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD CONSTRAINT `delivery_notes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notes_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_notes_ibfk_5` FOREIGN KEY (`sales_order_no`) REFERENCES `sales_orders` (`sales_order_no`),
  ADD CONSTRAINT `delivery_notes_ibfk_6` FOREIGN KEY (`delivery_order_no`) REFERENCES `delivery_orders` (`delivery_order_no`),
  ADD CONSTRAINT `delivery_notess_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notess_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD CONSTRAINT `delivery_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_orders_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `delivery_orders_ibfk_5` FOREIGN KEY (`sales_order_no`) REFERENCES `sales_orders` (`sales_order_no`);

--
-- Ketidakleluasaan untuk tabel `delivery_schedules`
--
ALTER TABLE `delivery_schedules`
  ADD CONSTRAINT `delivery_schedules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_schedules_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `departements_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dol_items`
--
ALTER TABLE `dol_items`
  ADD CONSTRAINT `dol_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dol_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dol_items_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `ecn`
--
ALTER TABLE `ecn`
  ADD CONSTRAINT `ecn_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ecn_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ecn_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `ecn_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `ecn_comment`
--
ALTER TABLE `ecn_comment`
  ADD CONSTRAINT `ecn_comment_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ecn_comment_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD CONSTRAINT `exchange_rates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `exchange_rates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`);

--
-- Ketidakleluasaan untuk tabel `forecasts`
--
ALTER TABLE `forecasts`
  ADD CONSTRAINT `forecasts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecasts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecasts_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `forecasts_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `forecast_histories`
--
ALTER TABLE `forecast_histories`
  ADD CONSTRAINT `forecast_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecast_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecast_histories_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `forecast_histories_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_item_calculation`
--
ALTER TABLE `generate_item_calculation`
  ADD CONSTRAINT `generate_item_calculation_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_item_calculation_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_loadcap`
--
ALTER TABLE `generate_loadcap`
  ADD CONSTRAINT `generate_loadcap_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_loadcap_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_loadcap_line`
--
ALTER TABLE `generate_loadcap_line`
  ADD CONSTRAINT `generate_loadcap_line_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_loadcap_line_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_loadcap_product`
--
ALTER TABLE `generate_loadcap_product`
  ADD CONSTRAINT `generate_loadcap_product_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_loadcap_product_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_machine_capacity`
--
ALTER TABLE `generate_machine_capacity`
  ADD CONSTRAINT `generate_machine_capacity_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_machine_capacity_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_mpp`
--
ALTER TABLE `generate_mpp`
  ADD CONSTRAINT `generate_mpp_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mpp_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `generate_mps`
--
ALTER TABLE `generate_mps`
  ADD CONSTRAINT `generate_mps_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `generate_mps_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mps_details`
--
ALTER TABLE `generate_mps_details`
  ADD CONSTRAINT `generate_mps_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_details_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `generate_mps_details_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mrp`
--
ALTER TABLE `generate_mrp`
  ADD CONSTRAINT `generate_mrp_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mrp_abcclass`
--
ALTER TABLE `generate_mrp_abcclass`
  ADD CONSTRAINT `generate_mrp_abcclass_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_abcclass_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_abcclass_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mrp_finals`
--
ALTER TABLE `generate_mrp_finals`
  ADD CONSTRAINT `generate_mrp_finals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_finals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mrp_finals_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `harnes_types`
--
ALTER TABLE `harnes_types`
  ADD CONSTRAINT `harnes_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `harnes_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `hsn`
--
ALTER TABLE `hsn`
  ADD CONSTRAINT `hsn_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hsn_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `income_statements`
--
ALTER TABLE `income_statements`
  ADD CONSTRAINT `income_statements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `income_statements_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `incoterms`
--
ALTER TABLE `incoterms`
  ADD CONSTRAINT `incoterms_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `incoterms_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `inventory_fg`
--
ALTER TABLE `inventory_fg`
  ADD CONSTRAINT `inventory_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_fg_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `inventory_rm`
--
ALTER TABLE `inventory_rm`
  ADD CONSTRAINT `inventory_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_rm_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `inventory_rm_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`);

--
-- Ketidakleluasaan untuk tabel `inventory_wip`
--
ALTER TABLE `inventory_wip`
  ADD CONSTRAINT `inventory_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_wip_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_materials`
--
ALTER TABLE `issued_materials`
  ADD CONSTRAINT `issued_material_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `issued_material_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_material_details`
--
ALTER TABLE `issued_material_details`
  ADD CONSTRAINT `issued_material_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_details_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_others`
--
ALTER TABLE `issued_others`
  ADD CONSTRAINT `issued_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_others_request`
--
ALTER TABLE `issued_others_request`
  ADD CONSTRAINT `issued_others_request_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_request_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_request_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_others_store`
--
ALTER TABLE `issued_others_store`
  ADD CONSTRAINT `issued_others_store_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_store_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_others_store_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_categories`
--
ALTER TABLE `item_categories`
  ADD CONSTRAINT `item_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_categories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_colors`
--
ALTER TABLE `item_colors`
  ADD CONSTRAINT `item_colors_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_colors_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_familys`
--
ALTER TABLE `item_familys`
  ADD CONSTRAINT `item_familys_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_familys_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_familys_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_family_subs`
--
ALTER TABLE `item_family_subs`
  ADD CONSTRAINT `item_family_subs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_family_subs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_family_subs_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`),
  ADD CONSTRAINT `item_family_subs_ibfk_5` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_fg`
--
ALTER TABLE `item_fg`
  ADD CONSTRAINT `item_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_kinds`
--
ALTER TABLE `item_kinds`
  ADD CONSTRAINT `item_kinds_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_kinds_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_mpq`
--
ALTER TABLE `item_mpq`
  ADD CONSTRAINT `item_mpq_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_mpq_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_platting`
--
ALTER TABLE `item_platting`
  ADD CONSTRAINT `item_platting_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_platting_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_rm`
--
ALTER TABLE `item_rm`
  ADD CONSTRAINT `item_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_rm_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `item_rm_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_sizes`
--
ALTER TABLE `item_sizes`
  ADD CONSTRAINT `item_sizes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_sizes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `journal_clearings`
--
ALTER TABLE `journal_clearings`
  ADD CONSTRAINT `journal_clearings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_clearings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `journal_postings`
--
ALTER TABLE `journal_postings`
  ADD CONSTRAINT `journal_postings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_postings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `journal_revaluations`
--
ALTER TABLE `journal_revaluations`
  ADD CONSTRAINT `journal_revaluations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_revaluations_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `journal_setups`
--
ALTER TABLE `journal_setups`
  ADD CONSTRAINT `journal_setups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_setups_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_setups_ibfk_3` FOREIGN KEY (`journal_type_id`) REFERENCES `journal_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `journal_types`
--
ALTER TABLE `journal_types`
  ADD CONSTRAINT `journal_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `line_productions`
--
ALTER TABLE `line_productions`
  ADD CONSTRAINT `line_productions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `line_productions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `logins`
--
ALTER TABLE `logins`
  ADD CONSTRAINT `logins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `machines`
--
ALTER TABLE `machines`
  ADD CONSTRAINT `machines_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `machines_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `makers`
--
ALTER TABLE `makers`
  ADD CONSTRAINT `makers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `makers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menus_no`
--
ALTER TABLE `menus_no`
  ADD CONSTRAINT `menus_no_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menus_no_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `new_barcode`
--
ALTER TABLE `new_barcode`
  ADD CONSTRAINT `new_barcode_ibafk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `new_barcode_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `new_barcode_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `new_barcode_fg`
--
ALTER TABLE `new_barcode_fg`
  ADD CONSTRAINT `new_barcode_fg_ibafk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `new_barcode_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `new_barcode_fg_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `ng_types`
--
ALTER TABLE `ng_types`
  ADD CONSTRAINT `ng_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ng_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `origins`
--
ALTER TABLE `origins`
  ADD CONSTRAINT `origins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `origins_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `os_fg`
--
ALTER TABLE `os_fg`
  ADD CONSTRAINT `os_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_fg_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `os_po`
--
ALTER TABLE `os_po`
  ADD CONSTRAINT `os_po_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_po_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `os_rm`
--
ALTER TABLE `os_rm`
  ADD CONSTRAINT `os_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_rm_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `os_rm_labels`
--
ALTER TABLE `os_rm_labels`
  ADD CONSTRAINT `os_rm_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_rm_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `os_supply`
--
ALTER TABLE `os_supply`
  ADD CONSTRAINT `os_supply_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_supply_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `os_wo`
--
ALTER TABLE `os_wo`
  ADD CONSTRAINT `os_wo_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_wo_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD CONSTRAINT `packing_lists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `packing_lists_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `packing_lists_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `packing_lists_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `packing_lists_ifk_5` FOREIGN KEY (`dn_number`) REFERENCES `delivery_notes` (`delivery_note_no`);

--
-- Ketidakleluasaan untuk tabel `payment_terms`
--
ALTER TABLE `payment_terms`
  ADD CONSTRAINT `ipayment_terms_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_terms_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `process`
--
ALTER TABLE `process`
  ADD CONSTRAINT `process_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `process_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `process_capacity`
--
ALTER TABLE `process_capacity`
  ADD CONSTRAINT `mst_capacity_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mst_capacity_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `process_main`
--
ALTER TABLE `process_main`
  ADD CONSTRAINT `process_main_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `process_main_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `process_main_sub`
--
ALTER TABLE `process_main_sub`
  ADD CONSTRAINT `process_main_sub_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `process_main_sub_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `process_main_sub_ibfk_3` FOREIGN KEY (`process_main_id`) REFERENCES `process_main` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `production_schedules`
--
ALTER TABLE `production_schedules`
  ADD CONSTRAINT `production_schedule_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_schedule_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_schedule_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `production_schedule_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_confirm`
--
ALTER TABLE `purchase_confirm`
  ADD CONSTRAINT `purchase_confirm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_confirm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_confirm_others`
--
ALTER TABLE `purchase_confirm_others`
  ADD CONSTRAINT `purchase_confirm_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_confirm_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_confirm_others_ibfk_3` FOREIGN KEY (`po_no`) REFERENCES `purchase_order_others` (`po_no`);

--
-- Ketidakleluasaan untuk tabel `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoices_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoices_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_invoices_ibfk_5` FOREIGN KEY (`journal_type_id`) REFERENCES `journal_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_invoice_journals`
--
ALTER TABLE `purchase_invoice_journals`
  ADD CONSTRAINT `purchase_invoice_journals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoice_journals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoice_journals_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `purchase_invoices` (`number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `purchase_order_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_ibfk_5` FOREIGN KEY (`request_no`) REFERENCES `purchase_requests` (`request_no`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_labels`
--
ALTER TABLE `purchase_order_labels`
  ADD CONSTRAINT `purchase_order_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_labels_ibfk_4` FOREIGN KEY (`receipt_id`) REFERENCES `purchase_order_receipts` (`receipt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_order_others`
--
ALTER TABLE `purchase_order_others`
  ADD CONSTRAINT `purchase_order_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_others_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `purchase_order_others_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_receipts`
--
ALTER TABLE `purchase_order_receipts`
  ADD CONSTRAINT `purchase_order_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipts_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_receipts_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_receipt_others`
--
ALTER TABLE `purchase_order_receipt_others`
  ADD CONSTRAINT `purchase_order_receipt_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipt_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipt_others_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_receipt_others_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `purchase_order_receipt_others_ibfk_5` FOREIGN KEY (`po_no`) REFERENCES `purchase_order_others` (`po_no`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_receipt_qas`
--
ALTER TABLE `purchase_order_receipt_qas`
  ADD CONSTRAINT `purchase_order_receipt_qas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipt_qas_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipt_qas_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_receipt_qas_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_rfq`
--
ALTER TABLE `purchase_order_rfq`
  ADD CONSTRAINT `purchase_order_rfq_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_rfq_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_rfq_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `purchase_order_rfq_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_services`
--
ALTER TABLE `purchase_order_services`
  ADD CONSTRAINT `purchase_order_services_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_services_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_services_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_requests_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_request_others`
--
ALTER TABLE `purchase_request_others`
  ADD CONSTRAINT `purchase_request_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_request_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_request_rfq`
--
ALTER TABLE `purchase_request_rfq`
  ADD CONSTRAINT `purchase_request_rfq_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_request_rfq_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_request_services`
--
ALTER TABLE `purchase_request_services`
  ADD CONSTRAINT `purchase_request_services_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_request_services_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_returns_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_returns_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `purchase_returns_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `qas`
--
ALTER TABLE `qas`
  ADD CONSTRAINT `qas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qas_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qas_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `qas_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `request_for_quotations`
--
ALTER TABLE `request_for_quotations`
  ADD CONSTRAINT `request_for_quotations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `request_for_quotations_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `request_for_quotations_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `request_for_quotations_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `return_materials`
--
ALTER TABLE `return_materials`
  ADD CONSTRAINT `return_materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_materials_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_materials_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `return_material_labels`
--
ALTER TABLE `return_material_labels`
  ADD CONSTRAINT `return_material_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_material_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_material_labels_ibfk_3` FOREIGN KEY (`return_id`) REFERENCES `return_materials` (`return_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rgp_transactions`
--
ALTER TABLE `rgp_transactions`
  ADD CONSTRAINT `rgp_transactions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rgp_transactions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rgp_transactions_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`),
  ADD CONSTRAINT `rgp_transactions_ibfk_4` FOREIGN KEY (`rgp_type_id`) REFERENCES `rgp_types` (`id`),
  ADD CONSTRAINT `rgp_transactions_ibfk_5` FOREIGN KEY (`currency`) REFERENCES `currencies` (`number`);

--
-- Ketidakleluasaan untuk tabel `rgp_transaction_labels`
--
ALTER TABLE `rgp_transaction_labels`
  ADD CONSTRAINT `rgp_transaction_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rgp_transaction_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rgp_transaction_labels_ibfk_3` FOREIGN KEY (`rgp_number`) REFERENCES `rgp_transactions` (`number`);

--
-- Ketidakleluasaan untuk tabel `rgp_types`
--
ALTER TABLE `rgp_types`
  ADD CONSTRAINT `rgp_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rgp_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoices_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoices_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_invoices_ibfk_5` FOREIGN KEY (`journal_type_id`) REFERENCES `journal_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_invoice_journals`
--
ALTER TABLE `sales_invoice_journals`
  ADD CONSTRAINT `sales_invoice_journals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoice_journals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoice_journals_ibfk_3` FOREIGN KEY (`number`) REFERENCES `sales_invoices` (`number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_order_deliveries`
--
ALTER TABLE `sales_order_deliveries`
  ADD CONSTRAINT `sales_order_deliveries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_order_deliveries_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_order_deliveries_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_order_deliveries_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_returns_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales_return_labels`
--
ALTER TABLE `sales_return_labels`
  ADD CONSTRAINT `sales_return_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_return_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sample_requests`
--
ALTER TABLE `sample_requests`
  ADD CONSTRAINT `sample_requests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_requests_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scan_item_receipts`
--
ALTER TABLE `scan_item_receipts`
  ADD CONSTRAINT `scan_item_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scan_item_receipts_fg`
--
ALTER TABLE `scan_item_receipts_fg`
  ADD CONSTRAINT `scan_item_receipts_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_receipts_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scan_item_rejections`
--
ALTER TABLE `scan_item_rejections`
  ADD CONSTRAINT `scan_item_rejections_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_rejections_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scan_item_rgp`
--
ALTER TABLE `scan_item_rgp`
  ADD CONSTRAINT `scan_item_rgp_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_rgp_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scraps`
--
ALTER TABLE `scraps`
  ADD CONSTRAINT `scraps_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scraps_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scraps_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `scrap_booking`
--
ALTER TABLE `scrap_booking`
  ADD CONSTRAINT `scrap_booking_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scrap_booking_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD CONSTRAINT `setting_menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_prodfam`
--
ALTER TABLE `setting_prodfam`
  ADD CONSTRAINT `setting_prodfam_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_prodfam_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_prodfam_types_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `setting_prodfam_types_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`);

--
-- Ketidakleluasaan untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD CONSTRAINT `setting_users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shipping_orders`
--
ALTER TABLE `shipping_orders`
  ADD CONSTRAINT `shipping_orders_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shipping_orders_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shipping_orders_fg_ibfk_3` FOREIGN KEY (`delivery_order_no`) REFERENCES `delivery_orders` (`delivery_order_no`);

--
-- Ketidakleluasaan untuk tabel `signatures`
--
ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `signatures_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `states_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `states_ibfk_3` FOREIGN KEY (`country_id`) REFERENCES `origins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stock_rm`
--
ALTER TABLE `stock_rm`
  ADD CONSTRAINT `stock_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stock_wip`
--
ALTER TABLE `stock_wip`
  ADD CONSTRAINT `stock_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_wip_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_fg`
--
ALTER TABLE `sto_fg`
  ADD CONSTRAINT `sto_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_fg_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_ot`
--
ALTER TABLE `sto_ot`
  ADD CONSTRAINT `sto_ot_ibfk_1` FOREIGN KEY (`location_other_number`) REFERENCES `warehouse_location_others` (`number`),
  ADD CONSTRAINT `sto_other_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_other_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_other_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_rm`
--
ALTER TABLE `sto_rm`
  ADD CONSTRAINT `sto_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_rm_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_store`
--
ALTER TABLE `sto_store`
  ADD CONSTRAINT `sto_store_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_store_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_store_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `sto_store_ibfk_4` FOREIGN KEY (`location_other_number`) REFERENCES `warehouse_location_others` (`number`);

--
-- Ketidakleluasaan untuk tabel `sto_wip`
--
ALTER TABLE `sto_wip`
  ADD CONSTRAINT `sto_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_wip_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD CONSTRAINT `supplier_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_items_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supplier_items_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `supplier_item_histories`
--
ALTER TABLE `supplier_item_histories`
  ADD CONSTRAINT `supplier_item_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_histories_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supplier_item_histories_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `supplier_item_prices`
--
ALTER TABLE `supplier_item_prices`
  ADD CONSTRAINT `supplier_item_price_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_price_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_price_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supplier_item_price_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `supplier_item_price_histories`
--
ALTER TABLE `supplier_item_price_histories`
  ADD CONSTRAINT `supplier_item_price_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_price_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_price_histories_ibfk_3` FOREIGN KEY (`supplier_item_price_id`) REFERENCES `supplier_item_prices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `supply_materials`
--
ALTER TABLE `supply_materials`
  ADD CONSTRAINT `supply_materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_materials_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_materials_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `supply_requestions`
--
ALTER TABLE `supply_requestions`
  ADD CONSTRAINT `supply_requestions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_requestions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_requestions_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `supply_sheets`
--
ALTER TABLE `supply_sheets`
  ADD CONSTRAINT `supply_sheets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_sheets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_sheets_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `supply_sheets_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supply_sheets_ibfk_5` FOREIGN KEY (`workorder`) REFERENCES `production_schedules` (`workorder`);

--
-- Ketidakleluasaan untuk tabel `taxes`
--
ALTER TABLE `taxes`
  ADD CONSTRAINT `taxes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `taxes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tds`
--
ALTER TABLE `tds`
  ADD CONSTRAINT `tds_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tds_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `terminals`
--
ALTER TABLE `terminals`
  ADD CONSTRAINT `terminals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `terminals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `trial_balances`
--
ALTER TABLE `trial_balances`
  ADD CONSTRAINT `trial_balances_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `trial_balances_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `umh`
--
ALTER TABLE `umh`
  ADD CONSTRAINT `umh_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `uom`
--
ALTER TABLE `uom`
  ADD CONSTRAINT `uom_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uom_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD CONSTRAINT `warehouse_locations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_locations_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `warehouse_location_items`
--
ALTER TABLE `warehouse_location_items`
  ADD CONSTRAINT `warehouse_location_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_location_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_location_items_ibfk_3` FOREIGN KEY (`warehouse_location_id`) REFERENCES `warehouse_locations` (`id`);

--
-- Ketidakleluasaan untuk tabel `warehouse_location_others`
--
ALTER TABLE `warehouse_location_others`
  ADD CONSTRAINT `warehouse_location_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_location_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wip_balances`
--
ALTER TABLE `wip_balances`
  ADD CONSTRAINT `wip_balances_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_balances_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_balances_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `wip_receipts`
--
ALTER TABLE `wip_receipts`
  ADD CONSTRAINT `wip_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipts_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `wip_receipts_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `wip_receipt_boxs`
--
ALTER TABLE `wip_receipt_boxs`
  ADD CONSTRAINT `wip_receipt_boxs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_boxs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wip_receipt_details`
--
ALTER TABLE `wip_receipt_details`
  ADD CONSTRAINT `wip_receipt_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_details_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `wip_receipt_details_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `wip_receipt_labels`
--
ALTER TABLE `wip_receipt_labels`
  ADD CONSTRAINT `wip_receipt_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_labels_ibfk_3` FOREIGN KEY (`checksheet_number`) REFERENCES `wip_receipts` (`checksheet_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wip_reports`
--
ALTER TABLE `wip_reports`
  ADD CONSTRAINT `wip_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_reports_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wos`
--
ALTER TABLE `wos`
  ADD CONSTRAINT `wos_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wos_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wos_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
