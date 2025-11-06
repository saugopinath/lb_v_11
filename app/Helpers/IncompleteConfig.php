<?php
namespace App\Helpers;

use App\SchemeBankAllowConfig;
use Illuminate\Support\Facades\DB;

class IncompleteConfig
{
    public static function getNameFailedOptions($scheme_id)
    {
        $schemeIncompleteConfig = SchemeBankAllowConfig::where('scheme_id', $scheme_id)
            ->where('incomplete_type', 11)
            ->first();

        $name_options = [];

        if ($schemeIncompleteConfig) {
            if ($schemeIncompleteConfig->new_bank_entry == TRUE) {
                $name_options[] = DB::table('m_name_valid_option')->where('id', 1)->first();
            }
            if ($schemeIncompleteConfig->keep_same == TRUE) {
                $name_options[] = DB::table('m_name_valid_option')->where('id', 2)->first();
            }
            if ($schemeIncompleteConfig->rejected == TRUE) {
                $name_options[] = DB::table('m_name_valid_option')->where('id', 3)->first();
            }
        }

        return $name_options;
    }

    public static function getBankUpdateAllow($scheme_id, $is_bank_failed = NULL, $dup_bank = NULL)
    {
        $allow_bank_update = NULL;
        if ($is_bank_failed) {
            if ($is_bank_failed == 1) {
                $schemeIncompleteConfig = SchemeBankAllowConfig::where('scheme_id', $scheme_id)->where('incomplete_type', 10)->first();
                $allow_bank_update = $schemeIncompleteConfig->new_bank_entry;
            } elseif ($is_bank_failed == 2) {
                $schemeIncompleteConfig = SchemeBankAllowConfig::where('scheme_id', $scheme_id)->where('incomplete_type', 11)->first();
                $allow_bank_update = $schemeIncompleteConfig->new_bank_entry;

            } elseif ($is_bank_failed == 3) {
                $schemeIncompleteConfig = SchemeBankAllowConfig::where('scheme_id', $scheme_id)->where('incomplete_type', 12)->first();
                $allow_bank_update = $schemeIncompleteConfig->new_bank_entry;
            } else {
                $allow_bank_update = FALSE;
            }
        } else {
            $allow_bank_update = TRUE;
        }

        if ($dup_bank) {
            $schemeIncompleteConfig = SchemeBankAllowConfig::where('scheme_id', $scheme_id)->where('incomplete_type', 3)->first();
            $allow_bank_update = $schemeIncompleteConfig->new_bank_entry;
        }

        return $allow_bank_update;
    }
}
