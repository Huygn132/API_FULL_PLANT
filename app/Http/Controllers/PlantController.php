<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PlantController extends Controller
{
    public function indexApi(Request $request, $selectedProjectId)
    {
        try {
            // lấy project id ở bảng plan
            $projectInPlanActuals = DB::table('t_project_plan_actuals')
                ->where('project_id', $selectedProjectId)
                ->get();

            // lấy danh sách tất cả nhân viên từ m_staff_datas
            $allStaffs = DB::table('m_staff_datas')
                ->select('id as staff_id', 'staff_type', DB::raw("CONCAT(last_name, ' ', first_name) AS full_name"))
                ->get();

            if (count($projectInPlanActuals) > 0) {
                $results = [];

                // show ra hết 
                $planActualStaffIds = [];
                foreach ($projectInPlanActuals as $planActual) {
                    $planActualStaffIds[] = $planActual->staff_id;

                    $staff = $allStaffs->firstWhere('staff_id', $planActual->staff_id);
                    if ($staff) {
                        $results[] = [
                            'planActualData' => $planActual,
                            'staffData' => $staff
                        ];
                    }
                }

                // show danh sách staff chưa có trong bảng bảng plant
                $remainingStaffIds = $allStaffs->pluck('staff_id')->diff($planActualStaffIds);
                foreach ($remainingStaffIds as $remainingStaffId) {
                    $staff = $allStaffs->firstWhere('staff_id', $remainingStaffId);
                    if ($staff) {
                        $results[] = [
                            'staffData' => $staff
                        ];
                    }
                }

                return response()->json($results);
            } else {
                $projectData = DB::table('t_projects')
                    ->where('id', $selectedProjectId)
                    ->first();

                if (!$projectData) {
                    return response()->json(['message' => 'Project not found'], 404);
                }

                return response()->json(['projectData' => $projectData, 'staffData' => $allStaffs]);
            }
        } catch (\Exception $e) {
            Log::error("Error in PlantController@indexApi: " . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function saveProjectPlanActuals(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'project_id' => 'required|integer|exists:t_projects,id',
                'staff_id' => 'required|array',
                'staff_id.*' => 'integer|exists:m_staff_datas,id',
                'this_year_4_plan' => 'nullable|numeric',
                'this_year_4_actual' => 'nullable|numeric',
                'this_year_5_plan' => 'nullable|numeric',
                'this_year_5_actual' => 'nullable|numeric',
                'this_year_6_plan' => 'nullable|numeric',
                'this_year_6_actual' => 'nullable|numeric',
                'this_year_7_plan' => 'nullable|numeric',
                'this_year_7_actual' => 'nullable|numeric',
                'this_year_8_plan' => 'nullable|numeric',
                'this_year_8_actual' => 'nullable|numeric',
                'this_year_9_plan' => 'nullable|numeric',
                'this_year_9_actual' => 'nullable|numeric',
                'this_year_10_plan' => 'nullable|numeric',
                'this_year_10_actual' => 'nullable|numeric',
                'this_year_11_plan' => 'nullable|numeric',
                'this_year_11_actual' => 'nullable|numeric',
                'this_year_12_plan' => 'nullable|numeric',
                'this_year_12_actual' => 'nullable|numeric',
                'nextyear_1_plan' => 'nullable|numeric',
                'nextyear_1_actual' => 'nullable|numeric',
                'nextyear_2_plan' => 'nullable|numeric',
                'nextyear_2_actual' => 'nullable|numeric',
                'nextyear_3_plan' => 'nullable|numeric',
                'nextyear_3_actual' => 'nullable|numeric',
            ]);

            foreach ($validatedData['staff_id'] as $staffId) {
                $existingRecord = DB::table('t_project_plan_actuals')
                    ->where('project_id', $validatedData['project_id'])
                    ->where('staff_id', $staffId)
                    ->first();

                $dataToInsertOrUpdate = $existingRecord ? (array) $existingRecord : [];

                if (!$existingRecord) {
                    $dataToInsertOrUpdate['staff_id'] = $staffId;
                    $dataToInsertOrUpdate['project_id'] = $validatedData['project_id'];
                }

                foreach ($validatedData as $key => $value) {
                    if (!is_null($value) && $key != 'staff_id') {
                        $dataToInsertOrUpdate[$key] = $value;
                    }
                }

                if ($existingRecord) {
                    DB::table('t_project_plan_actuals')
                        ->where('project_id', $validatedData['project_id'])
                        ->where('staff_id', $staffId)
                        ->update($dataToInsertOrUpdate);
                } else {
                    DB::table('t_project_plan_actuals')->insert($dataToInsertOrUpdate);
                }
            }


            return response()->json(['message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            Log::error("Error in PlantController@saveProjectPlanActuals: " . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
