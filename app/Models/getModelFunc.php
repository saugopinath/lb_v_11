<?php

namespace App\Models;

use App\Models\DataSourceCommon;

class getModelFunc
{

      public function SoureTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ss_lb_mapping';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function pensiontable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'dist_' . $dist_code . '.beneficiary';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }

      public function getDraftAadharTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_aadhar_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }

      public function getDraftIdentificationTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_aadhar_details_temp';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getDraftPersonalTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_personal_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }

      public function getDraftContactTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_contact_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getDraftBankTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_bank_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getDraftProfileImageTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_profile_image';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getDraftEncolserTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_attach_documents';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getDraftOtherTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_declaration_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getacceptreject($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_accept_reject_info';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getSwsTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ss_lb_mapping';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getProfileImageTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_profile_image';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getEncolserTable($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.draft_ben_attach_documents';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getAcceptRejectTable($dist_code, $source_type)
      {
            //$DataSourceCommon = new DataSourceCommon;
            $table = 'lb_pension.ben_accept_reject_info';
            // $DataSourceCommon->setTable('' . $tableBen);
            return $table;
      }
      public function getBenAcceptReject($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_accept_reject_info';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getBenReject($dist_code, $source_type)
      {
            $DataSourceCommon = new DataSourceCommon;
            $tableBen = 'lb_pension.ben_reject_details';
            $DataSourceCommon->setTable('' . $tableBen);
            return $DataSourceCommon;
      }
      public function getSchemaDetails($finYear = NULL)
      {

            if (!empty($finYear)) {
                  // $explodeYear = explode("-", $finYear);
                  // $firstPart = substr($explodeYear[0], 2);
                  // $secondPart = substr($explodeYear[1], 2);
                  // $schema = 'trx_mgmt_' . $firstPart . $secondPart;
                  $currentyear = date('Y');
                  $prevYear = date('Y') - 1;
                  $nextyear = date('Y') + 1;
                  $month = date('n');
                  if ($month > 3) {
                        $cur_fin_year = $currentyear .'-' .$nextyear;
                  } else {
                         $cur_fin_year = $prevYear .'-' .($prevYear+1);
                  }
                   // $cur_fin_year = $currentyear .'-' .$nextyear;
                  if ($finYear == $cur_fin_year) {
                        $schema = 'trx_mgmt_cur_fy';
                  }
                  else {
                        $explodeYear = explode("-", $finYear);
                        $firstPart = substr($explodeYear[0], 2);
                        $secondPart = substr($explodeYear[1], 2);
                        $schema = 'trx_mgmt_' . $firstPart . $secondPart;
                        // $schema = 'trx_mgmt_cur_fy';
                  }

            } else {
                  // $currentyear = date('Y');
                  // $prevYear = date('Y') - 1;
                  // $nextyear = date('Y') + 1;
                  // $month = date('n');
                  // if ($month > 3) {
                  //       $schema = 'trx_mgmt_' . substr($currentyear, 2, 4) . substr($nextyear, 2, 4);
                  // } else {
                  //       $schema = 'trx_mgmt_' . substr($prevYear, 2, 4) . substr($prevYear + 1, 2, 4);
                  // }
                  $schema = 'payment';
            }

            //echo $schema;die;
            return $schema;
      }
      public function getTable($dist_code, $source_type, $table_code, $is_draft = NULL)
      {
            if ($table_code == 1) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.draft_ben_personal_details';
                  } else {
                        $table = 'lb_scheme.ben_personal_details';
                  }
            } else if ($table_code == 2) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.ben_aadhar_details';
                  } else {
                        $table = 'lb_scheme.ben_aadhar_details';
                  }
            } else if ($table_code == 3) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.draft_ben_contact_details';
                  } else {
                        $table = 'lb_scheme.ben_contact_details';
                  }
            } else if ($table_code == 4) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.draft_ben_bank_details';
                  } else {
                        $table = 'lb_scheme.ben_bank_details';
                  }
            } else if ($table_code == 5) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.ben_profile_image';
                  } else {
                        $table = 'lb_scheme.ben_profile_image';
                  }
            } else if ($table_code == 6) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.ben_attach_documents';
                  } else {
                        $table = 'lb_scheme.ben_attach_documents';
                  }
            } else if ($table_code == 7) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.draft_ben_declaration_details';
                  } else {
                        $table = 'lb_scheme.ben_declaration_details';
                  }
            } else if ($table_code == 8) {
                  $table = 'lb_scheme.ss_lb_mapping';
            } else if ($table_code == 9) {
                  $table = 'lb_scheme.ben_accept_reject_info';
            } else if ($table_code == 10) {
                  $table = 'lb_scheme.ben_reject_details';
            }
            return $table;
      }
      public function getTableFaulty($dist_code, $source_type, $table_code, $is_draft = NULL)
      {
            if ($table_code == 1) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_personal_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_personal_details';
                  }
            } else if ($table_code == 2) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.ben_aadhar_details';
                  } else {
                        $table = 'lb_scheme.ben_aadhar_details';
                  }
            } else if ($table_code == 3) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_contact_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_contact_details';
                  }
            } else if ($table_code == 4) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_bank_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_bank_details';
                  }
            } else if ($table_code == 5) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_ben_profile_image';
                  } else {
                        $table = 'lb_scheme.faulty_ben_profile_image';
                  }
            } else if ($table_code == 6) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_ben_attach_documents';
                  } else {
                        $table = 'lb_scheme.faulty_ben_attach_documents';
                  }
            } else if ($table_code == 7) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_declaration_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_declaration_details';
                  }
            } else if ($table_code == 8) {
                  $table = 'lb_scheme.ss_lb_mapping';
            } else if ($table_code == 9) {
                  $table = 'lb_scheme.ben_accept_reject_info';
            } else if ($table_code == 10) {
                  $table = 'lb_scheme.ben_reject_details';
            }
            else if($table_code == 11){
                  $table = 'lb_scheme.faulty_ben_personal_details_migrate';
            }
            return $table;
      }
      public function getTableFaultyWOutDoc($dist_code, $source_type, $table_code, $is_draft = NULL)
      {
            if ($table_code == 1) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_personal_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_personal_details';
                  }
            } else if ($table_code == 2) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.ben_aadhar_details';
                  } else {
                        $table = 'lb_scheme.ben_aadhar_details';
                  }
            } else if ($table_code == 3) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_contact_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_contact_details';
                  }
            } else if ($table_code == 4) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_bank_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_bank_details';
                  }
            } else if ($table_code == 5) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_ben_profile_image';
                  } else {
                        $table = 'lb_scheme.faulty_ben_profile_image';
                  }
            } else if ($table_code == 6) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_ben_attach_documents';
                  } else {
                        $table = 'lb_scheme.faulty_ben_attach_documents';
                  }
            } else if ($table_code == 7) {
                  if ($is_draft == 1) {
                        $table = 'lb_scheme.faulty_draft_ben_declaration_details';
                  } else {
                        $table = 'lb_scheme.faulty_ben_declaration_details';
                  }
            } else if ($table_code == 8) {
                  $table = 'lb_scheme.ss_lb_mapping';
            } else if ($table_code == 9) {
                  $table = 'lb_scheme.ben_accept_reject_info';
            } else if ($table_code == 10) {
                  $table = 'lb_scheme.ben_reject_details';
            }
            return $table;
      }
}
