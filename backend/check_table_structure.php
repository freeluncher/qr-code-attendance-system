<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ATTENDANCES TABLE STRUCTURE ===" . PHP_EOL;

$columns = DB::select("SELECT column_name, data_type, is_nullable
                       FROM information_schema.columns
                       WHERE table_name = 'attendances'
                       ORDER BY ordinal_position");

foreach ($columns as $column) {
    echo $column->column_name . " (" . $column->data_type . ") - Nullable: " . $column->is_nullable . PHP_EOL;
}
