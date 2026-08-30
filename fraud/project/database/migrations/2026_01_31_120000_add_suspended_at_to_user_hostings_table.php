<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * হোস্টিং এক্সপায়ারির আগে ৭ দিন সাস্পেন্ড গ্রেস পিরিয়ড ট্র্যাক করতে।
     */
    public function up(): void
    {
        Schema::table('user_hostings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_hostings', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('status')->comment('কখন সাস্পেন্ড হয়েছে (৭ দিন পর এক্সপায়ার)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_hostings', function (Blueprint $table) {
            if (Schema::hasColumn('user_hostings', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
        });
    }
};
