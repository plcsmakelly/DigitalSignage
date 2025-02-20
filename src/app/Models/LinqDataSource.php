<?php

namespace App\Models;

class LinqDataSource
{
    private function getWebRequest($url) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "DigitalSignage/1.0");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getMenuDataFromId($id) {
        $response = $this->getWebRequest("https://api.linqconnect.com/api/FamilyMenuIdentifier?identifier=".$id);
        if (!$response) {
            return false;
        }

        try {
            $data = json_decode($response);

            $districtid = $data->DistrictId;

            if ($districtid == "00000000-0000-0000-0000-000000000000") {
                return false;
            }

            $districtname = $data->DistrictName;
            $buildings = $data->Buildings;
            $selectedbuilding = $data->SelectedBuildingId;

            $buildingname = "Unknown Building";

            foreach($buildings as $building) {
                if ($building->BuildingId == $selectedbuilding) {
                    $buildingname = $building->Name;
                }
            }

            return array(
                'district_id' => $districtid,
                'district_name' => $districtname,
                'building_id' => $selectedbuilding,
                'building_name' => $buildingname
            );
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getMenuForDay($district, $building, $date) {
        $response = $this->getWebRequest("https://api.linqconnect.com/api/FamilyMenu?buildingId=".$building."&districtId=".$district."&startDate=".$date."&endDate=".$date);

        try {
            $data = json_decode($response);

            if (sizeof($data->FamilyMenuSessions) < 1) {
                return false;
            }
            if (sizeof($data->FamilyMenuSessions[0]->MenuPlans) < 1) {
                return false;
            }
            if (sizeof($data->FamilyMenuSessions[0]->MenuPlans[0]->Days) < 1) {
                return false;
            }
            $menuday = $data->FamilyMenuSessions[0]->MenuPlans[0]->Days[0];

            if (sizeof($menuday->MenuMeals) < 1) {
                return false;
            }
            $categories = $menuday->MenuMeals[0]->RecipeCategories;

            $overallItems = array();

            foreach($categories as $category) {
                $items = $category->Recipes;

                $categoryArray = array();

                foreach($items as $item) {
                    $categoryArray[] = array("name" => trim($item->RecipeName), "item_id" => $item->ItemId);
                }

                $overallItems[$category->CategoryName] = $categoryArray;
            }

            return $overallItems;
        } catch (Exception $ex) {
            return false;
        }
    }
}
