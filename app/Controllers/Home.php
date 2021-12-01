<?php

namespace App\Controllers;

class Home extends BaseController
{
   

    public function cors_options(){
        return $this->response->setHeader('Access-Control-Allow-Origin', '*') //for allow any domain, insecure
        ->setHeader('Access-Control-Allow-Headers', '*') //for allow any headers, insecure
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE') //method allowed
        ->setStatusCode(200);
    }

    public function store_area()
    {
        $db = \Config\Database::connect();

        $storeArea = $db->table('store_area')->get()->getResult();

        return $this->response->setStatusCode(200)->setJson($storeArea);
    }

    public function report(){

            $area = $this->request->getVar('area');
            $dateFrom = $this->request->getVar('dateFrom');
            $dateTo = $this->request->getVar('dateTo');

            $db = \Config\Database::connect();

            $chart = $db->query("SELECT area_name, sum(compliance)/(select count(*) from report_product) * 100 as percentage
                                FROM report_product join store on report_product.store_id = store.store_id 
                                join store_area on store_area.area_id = store.area_id 
                                where tanggal between '$dateFrom' and '$dateTo'
                                group by area_name order by area_name");


            $table = $db->query("SELECT brand_name, 
                                  (sum(case when z.area_id = 1 then compliance end)/(select count(*) from report_product)) * 100 as jakarta, 
                                  (sum(case when z.area_id = 2 then compliance end)/(select count(*) from report_product)) * 100 as jawa_barat, 
                                  (sum(case when z.area_id = 3 then compliance end)/(select count(*) from report_product)) * 100 as kalimantan, 
                                  (sum(case when z.area_id = 4 then compliance end)/(select count(*) from report_product)) * 100 as jawa_tengah,
                                  (sum(case when z.area_id = 5 then compliance end)/(select count(*) from report_product)) * 100 as bali 
                                  from product 
                                  join product_brand on product_brand.brand_id = product.brand_id 
                                  join report_product on report_product.product_id = product.product_id 
                                  join (select store_id,area_name, store_area.area_id from store join store_area on store_area.area_id = store.area_id) as z on z.store_id = report_product.store_id 
                                  where tanggal between '$dateFrom' and '$dateTo' 
                                  group by brand_name order by area_name");
          
            return $this->response->setStatusCode(200)->setJson([
                'chart' => $chart->getResult(),
                'table' => $table->getResult()
            ]);
  
    }

  
}
