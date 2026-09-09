<?php
namespace Database\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Models\Organization;
use Illuminate\Database\Seeder;
use App\Modules\Operation\Infrastructure\Persistence\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder {
    public function run(): void {
        $org=config('app.default_organization_id') ?: Organization::value('id');
        if(!$org) return;
        $items=[
            ['SURT_ABARROTES','Surtido de abarrotes',false],['SURT_FRUTA','Surtido de frutas y verduras',false],
            ['SURT_GRANEL','Surtido de semillas y condimentos a granel',false],['SURT_TLAPALERIA','Surtido de tlapalería',false],
            ['SURT_PAPELERIA','Surtido de papelería',false],['OPER_LUZ','Luz',false],['OPER_AGUA','Agua',false],
            ['OPER_INTERNET','Internet y telefonía',false],['OPER_RENTA','Renta',true],['OPER_GAS','Gas',false],
            ['OPER_LIMPIEZA','Limpieza e insumos',false],['OPER_EMPAQUE','Bolsas y empaques',false],
            ['TRANS_GASOLINA','Gasolina',false],['TRANS_FLETE','Flete y transporte',false],
            ['MANT_REPARACION','Mantenimiento y reparaciones',false],['SERV_COMISION','Comisiones bancarias / terminal',false],
            ['SERV_CONTABLE','Servicios contables',true],['PERSONAL_ADELANTO','Adelantos al personal',true],
            ['INVERSION_EQUIPO','Compra de equipo / mobiliario',true],['OTRO','Otro gasto',false]
        ];
        foreach($items as [$code,$name,$approval])
            ExpenseCategory::updateOrCreate(['organization_id'=>$org,'code'=>$code],['name'=>$name,'requires_approval'=>$approval,'is_active'=>true]);
    }
}
