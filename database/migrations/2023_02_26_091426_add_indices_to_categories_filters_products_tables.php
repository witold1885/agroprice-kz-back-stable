<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndicesToCategoriesFiltersProductsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id');
        });

        Schema::table('category_filter_groups', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('filter_group_id');
        });

        Schema::table('filters', function (Blueprint $table) {
            $table->index('filter_group_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('location_id');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('product_id');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('product_contacts', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('filter_group_id');
            $table->index('filter_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });

        Schema::table('category_filter_groups', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'filter_group_id']);
        });

        Schema::table('filters', function (Blueprint $table) {
            $table->dropIndex(['filter_group_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'location_id']);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'product_id']);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('product_contacts', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'filter_group_id', 'filter_id']);
        });
    }
}
