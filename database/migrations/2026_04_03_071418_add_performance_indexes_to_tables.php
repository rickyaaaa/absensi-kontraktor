<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AUDIT FIX #3 — Database Indexing for Performance
 *
 * WHY: Without indexes, MySQL does a full table scan on every query that filters
 * by employee_id, date, or status. On tables with thousands of rows, this
 * becomes catastrophically slow. Indexes reduce lookup time from O(n) to O(log n).
 *
 * - attendances.employee_id  — used in every "get my attendance" query
 * - attendances.date         — used in dashboard "today's attendance" queries
 * - attendances.location_status — used in monitoring/filtering
 * - payrolls.employee_id     — used in every payroll history query
 * - payrolls.status          — used in pending payroll count
 * - payrolls.period_end      — used for ordering
 * - kasbons.employee_id      — used in every kasbon listing query
 * - kasbons.date             — used for ordering/filtering
 * - overtimes.employee_id    — used in every overtime query
 * - overtimes.date           — used for filtering
 * - employees.user_id        — foreign key used in all joins/eager loads
 * - employees.role_level     — used in supervisor dashboard filter
 */
return new class extends Migration
{
    public function up(): void
    {
        // attendances table indexes
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('employee_id', 'idx_attendances_employee_id');
            $table->index('date', 'idx_attendances_date');
            $table->index(['employee_id', 'date'], 'idx_attendances_employee_date');
            $table->index('location_status', 'idx_attendances_location_status');
        });

        // payrolls table indexes
        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('employee_id', 'idx_payrolls_employee_id');
            $table->index('status', 'idx_payrolls_status');
            $table->index('period_end', 'idx_payrolls_period_end');
        });

        // kasbons table indexes
        Schema::table('kasbons', function (Blueprint $table) {
            $table->index('employee_id', 'idx_kasbons_employee_id');
            $table->index('date', 'idx_kasbons_date');
        });

        // overtimes table indexes
        Schema::table('overtimes', function (Blueprint $table) {
            $table->index('employee_id', 'idx_overtimes_employee_id');
            $table->index('date', 'idx_overtimes_date');
        });

        // employees table indexes
        Schema::table('employees', function (Blueprint $table) {
            $table->index('user_id', 'idx_employees_user_id');
            $table->index('role_level', 'idx_employees_role_level');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_employee_id');
            $table->dropIndex('idx_attendances_date');
            $table->dropIndex('idx_attendances_employee_date');
            $table->dropIndex('idx_attendances_location_status');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex('idx_payrolls_employee_id');
            $table->dropIndex('idx_payrolls_status');
            $table->dropIndex('idx_payrolls_period_end');
        });

        Schema::table('kasbons', function (Blueprint $table) {
            $table->dropIndex('idx_kasbons_employee_id');
            $table->dropIndex('idx_kasbons_date');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropIndex('idx_overtimes_employee_id');
            $table->dropIndex('idx_overtimes_date');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_user_id');
            $table->dropIndex('idx_employees_role_level');
        });
    }
};
