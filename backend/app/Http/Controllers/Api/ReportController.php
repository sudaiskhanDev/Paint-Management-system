<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type;

        // =========================
        // DATE RANGE LOGIC
        // =========================
        if ($request->from && $request->to) {
            $from = $request->from;
            $to = $request->to;
        } else {

            switch ($type) {

                case 'weekly':
                    $from = date('Y-m-d', strtotime('-7 days'));
                    $to = date('Y-m-d');
                    break;

                case 'monthly':
                    $from = date('Y-m-d', strtotime('-30 days'));
                    $to = date('Y-m-d');
                    break;

                case 'yearly':
                    $from = date('Y-m-d', strtotime('-365 days'));
                    $to = date('Y-m-d');
                    break;

                default: // daily
                    $from = date('Y-m-d');
                    $to = date('Y-m-d');
            }
        }

        // =========================
        // TOTAL SALES
        // =========================
        $totalSales = Sale::whereBetween('sale_date', [$from, $to])
            ->sum('total_amount');

        // =========================
        // TOTAL PURCHASES
        // =========================
        $totalPurchases = Purchase::whereBetween('purchase_date', [$from, $to])
            ->sum('total_amount');

        // =========================
        // PROFIT
        // =========================
        $profit = $totalSales - $totalPurchases;

        return response()->json([
            'from' => $from,
            'to' => $to,
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'profit' => $profit,
        ]);
    }
}