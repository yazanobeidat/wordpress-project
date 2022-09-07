<?php

namespace ACFWF\Models\Objects\Report_Widgets;

use ACFWF\Abstracts\Abstract_Report_Widget;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Orders_Discounted extends Abstract_Report_Widget
{
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Report Widget object.
     *
     * @since 4.3
     * @access public
     * 
     * @param Date_Period_Range $report_period Date period range object.
     */
    public function __construct($report_period)
    {
        $this->key         = 'orders_discounted';
        $this->widget_name = __('Orders Discounted', 'advanced-coupons-for-woocommerce-free');
        $this->type        = 'big_number';
        $this->description = __('Orders Discounted', 'advanced-coupons-for-woocommerce-free');

        // build report data.
        parent::__construct($report_period);
    }

    /*
    |--------------------------------------------------------------------------
    | Query methods
    |--------------------------------------------------------------------------
    */

    /**
     * Query report data freshly from the database.
     * 
     * @since 4.3
     * @access protected
     */
    protected function _query_report_data()
    {
        global $wpdb;

        $start_period = $this->report_period->start_period->format('Y-m-d H:i:s');
        $end_period   = $this->report_period->end_period->format('Y-m-d H:i:s');
        $statuses     = implode("','wc-", array_map('esc_sql', wc_get_is_paid_statuses()));

        $query = "SELECT DISTINCT COUNT(o.ID) FROM {$wpdb->posts} AS o
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON (oi.order_id = o.ID AND oi.order_item_type = 'coupon')
            INNER JOIN {$wpdb->postmeta} AS om1 ON (om1.post_id = o.ID AND om1.meta_key = '_paid_date')
            WHERE CONVERT(om1.meta_value, DATETIME) BETWEEN '{$start_period}' AND '{$end_period}'
            AND o.post_status IN ('wc-{$statuses}')
        ";

        $this->raw_data = intval($wpdb->get_var($query));
    }

    /*
    |--------------------------------------------------------------------------
    | Utility methods
    |--------------------------------------------------------------------------
     */

    /**
     * NOTE: This method needs to be override on the child class.
     * 
     * @since 4.3
     * @access public
     */
    protected function _format_report_data()
    {
        $this->title = \ACFWF()->Helper_Functions->format_integer_for_display($this->raw_data);
    }
}