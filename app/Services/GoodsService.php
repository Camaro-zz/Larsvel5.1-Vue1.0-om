<?php
namespace App\Services;


use App\Models\OmGoods;
use App\Models\OmGoodsCat;
use App\Models\OmGoodsImg;
use App\Models\OmGoodsMfrs;
use App\Models\OmGoodsSupplier;
use App\Models\OmSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GoodsService extends BaseService {

    public function __construct(){
        $this->uid = Auth::user()->id;
    }

    /**
     * 添加商品
     */
    public function addGoods($data){
        $data = array(
            'cat_id' => 1,
            'product_sn' => '12121',
            'en_name' => 'weq',
            'cn_name' => '撒打算',
            'car_types' => 'sadsadasdsds',
            'mark' => 'asdads',
            'img' => 'asadsadas',
            'imgs' => array(
                ['img'=>'sadsasad','sort'=>0],
                ['img'=>'sadsasad','sort'=>0],
                ['img'=>'sadsasad','sort'=>0]
            )
        );
        $v = $this->goodsValidator($data);
        if(!$v['status']){
            return $v;
        }
        $goods_data = $data;
        unset($goods_data['imgs']);
        $goods_data['uid'] = $this->uid;
        $goods = OmGoods::create($goods_data);
        if($goods->id){
            isset($data['imgs']) && $this->setGoodsImgs($goods, $data['imgs']);
            return ['status'=>true, 'data'=>$goods];
        }else{
            return ['status'=>false, 'msg'=>'产品添加失败'];
        }
    }

    /**
     * 添加供应商关联商品
     * @param $goods_id
     * @param $data
     */
    public function addSupplierGoods($goods_id, $data){
        $goods_id = 1;
        $data = array(
            'supplier_id' => 1,
            'price' => '10.2',
            'tax' => '10.2',
            'num' => '10',
            'length' => '10.2',
            'width' => '10.2',
            'height' => '10.2',
            'gw' => '10.2',
            'nw' => '10.2',
            'mfrs_name' => 'safdsadsad',
        );

        $goods = OmGoods::where('id', $goods_id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $v = $this->supplierGoodsValidator($data);
        if(!$v['status']){
            return $v;
        }
        $data['goods_id'] = $goods_id;
        $data['uid'] = $this->uid;
        $res = OmGoodsSupplier::create($data);
        if($res->id){
            return ['status'=>true, 'data'=>$res];
        }else{
            return ['status'=>false, 'msg'=>'产品添加失败'];
        }
    }

    public function addGoodsMfrs($data){
        $goods_id = isset($data['goods_id']) ? intval($data['goods_id']) : 0;
        $goods = OmGoods::where('id', $goods_id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $v = $this->supplierGoodsValidator($data);
        if(!$v['status']){
            return $v;
        }
        $data['uid'] = $this->uid;
        $res = OmGoodsMfrs::create($data);
        if($res->id){
            return ['status'=>true, 'data'=>$res];
        }else{
            return ['status'=>false, 'msg'=>'生产商添加失败'];
        }
    }

    public function editGoodsMfrs($id, $data){
        $mfrs = OmGoodsMfrs::where('id',$id)->first();
        if(!$mfrs){
            return ['status'=>false,'msg'=>'记录不存在'];
        }
        $goods_id = isset($data['goods_id']) ? intval($data['goods_id']) : 0;
        $goods = OmGoods::where('id', $goods_id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $v = $this->supplierGoodsValidator($data);
        if(!$v['status']){
            return $v;
        }
        $res = OmGoodsMfrs::where('id',$id)->update($data);
        if($res->id){
            return ['status'=>true, 'data'=>$res];
        }else{
            return ['status'=>false, 'msg'=>'生产商更新失败'];
        }
    }

    public function editGoods($id, $data){
        $goods = OmGoods::where('id',$id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $v = $this->goodsValidator($data,$id);
        if(!$v['status']){
            return $v;
        }
        $goods_data = $data;
        unset($goods_data['imgs']);
        $goods = OmGoods::where(array('id'=>$id,'is_deleted'=>0))->update($goods_data);

        if($goods){
            $goods = OmGoods::where('id',$id)->first();
            isset($data['imgs']) && $this->setGoodsImgs($goods, $data['imgs']);
            return ['status'=>true, 'data'=>$goods];
        }else{
            return ['status'=>false, 'msg'=>'产品更新失败'];
        }
    }

    public function editSupplierGoods($id, $data){
        $goods = OmGoodsSupplier::where('id',$id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $v = $this->supplierGoodsValidator($data,$id);
        if(!$v['status']){
            return $v;
        }
        $goods = OmGoodsSupplier::where(array('id'=>$id,'is_deleted'=>0))->update($data);

        if($goods){
            $goods = OmGoodsSupplier::where('id',$id)->first();
            return ['status'=>true, 'data'=>$goods];
        }else{
            return ['status'=>false, 'msg'=>'产品更新失败'];
        }
    }

    public function getGoods($id){
        $goods = OmGoods::where('id',$id)->first();
        if(!$goods){
            return ['status'=>false, 'msg'=>'产品不存在'];
        }
        $goods['supplier_goods'] = OmGoodsSupplier::where(array('goods_id'=>$id))->orderBy('sort','DESC')->get();
        $goods['supplier_goods_count'] = count($goods['supplier_goods']);
        if(isset($goods['supplier_goods'][0])){
            $goods['price'] = $goods['supplier_goods'][0]['price'];
            $goods['tax'] = $goods['supplier_goods'][0]['tax'];
            $goods['num'] = $goods['supplier_goods'][0]['num'];
            $goods['length'] = $goods['supplier_goods'][0]['length'];
            $goods['width'] = $goods['supplier_goods'][0]['width'];
            $goods['height'] = $goods['supplier_goods'][0]['height'];
            $goods['gw'] = $goods['supplier_goods'][0]['gw'];
            $goods['nw'] = $goods['supplier_goods'][0]['nw'];
            $goods['mfrs_name'] = $goods['supplier_goods'][0]['mfrs_name'];
        }
        return ['status'=>true,'data'=>$goods];
    }

    public function getGoodses($data){
        $offset = isset($data['offset']) ? $data['offset'] : 0;
        $limit = isset($data['limit']) ? $data['limit'] : 20;
        $query = OmGoods::leftJoin('om_goods_cat as cat','cat.id','=','om_goods.cat_id')
                        ->select('om_goods.id','om_goods.product_sn','om_goods.en_name','om_goods.cn_name','om_goods.img','om_goods.car_types','cat.name as cat_name')->where('om_goods.is_deleted',0);
        if(isset($data['cn_name']) && $data['cn_name']){
            $query->where('om_goods.cn_name', 'like', '%' . $data['cn_name'] . '%');
        }
        if(isset($data['en_name']) && $data['en_name']){
            $query->where('om_goods.en_name', 'like', '%' . $data['en_name'] . '%');
        }
        if(isset($data['cat_id']) && $data['cat_id'] > 0){
            $query->where('om_goods.cat_id', '=', $data['cat_id']);
        }
        $result['_count'] = $query->count();
        $query->skip($offset);
        $query->take($limit);
        $result['data'] = $query->get();
        if ($result['data']) {
            foreach ($result['data'] as &$v) {
                //DB::connection()->enableQueryLog();
                $v['prop'] = OmGoodsSupplier::leftJoin('om_supplier as sup','sup.id','=','om_goods_supplier.supplier_id')
                                            ->select('sup.name','sup.supplier_sn','om_goods_supplier.*')
                                            ->where(array('om_goods_supplier.goods_id'=>$v['id'],'om_goods_supplier.is_deleted'=>0))->orderBy('sort', 'DESC')->first();
                if(!$v['prop']){
                    $v['prop'] = '';
                }
                $v['mfrs'] = OmGoodsMfrs::select('mfrs_sn','mfrs_name','sort')->where(array('goods_id'=>$v['id'],'is_deleted'=>0))->orderBy('sort', 'DESC')->first();
                if(!$v['mfrs']){
                    $v['mfrs'] = '';
                }
                //dump(DB::getQueryLog());
            }
        }

        return $result;
    }

    public function deleteGoodses($ids){
        $ids = explode(',',$ids);
        $delete = OmGoods::whereIn('id',$ids)->update(array('is_deleted'=>1));
        return ['status'=>true];
    }

    //验证规则
    public function goodsValidator($data, $id=''){
        $message = [
            'product_sn.required' => '产品编号不能为空',
            'product_sn.unique' => '产品编号已存在',
            'en_name.required' => '英文品名不.能为空',
            'cn_name.required' => '中文品名不能为空',
        ];

        $rule = [
            'product_sn' => 'required|unique:om_goods,product_sn,'.$id,
            'en_name' => 'required',
            'cn_name' => 'required',
        ];
        $res = $this->doValidate($data,$rule,$message);
        return $res;
    }

    //验证规则
    public function supplierGoodsValidator($data){
        $message = [
            'price.required' => '采购价不能为空',
            'price.numeric' => '采购价只能为数值',
            'tax.required' => '税不能为空',
            'tax.numeric' => '税只能为数值',
            'num.required' => '装箱数不能为空',
            'num.integer' => '装箱数只能是整数',
            'length.numeric' => '规格长只能是数值',
            'width.numeric' => '规格宽只能是数值',
            'height.numeric' => '规格高只能是数值',
            'gw.numeric' => '毛重只能是数值',
            'nw.numeric' => '净重长只能是数值',
        ];

        $rule = [
            'price' => 'required|numeric',
            'tax' => 'required|numeric',
            'num' => 'required|integer',
            'length' => 'numeric',
            'width' => 'numeric',
            'height' => 'numeric',
            'gw' => 'numeric',
            'nw' => 'numeric',
        ];

        $res = $this->doValidate($data,$rule,$message);
        return $res;
    }

    public function mfrsGoodsValidator($data){
        $message = [
            'mfrs_sn.required' => '原厂编号不能为空',
            'mfrs_name.required' => '生产商名称不能为空',
        ];

        $rule = [
            'mfrs_sn' => 'required',
            'mfrs_name' => 'required',
        ];
        $res = $this->doValidate($data,$rule,$message);
        return $res;
    }

    protected function setGoodsImgs($goods, $imgs) {
        $goods->imgs()->delete();
        $imgsData = array();
        foreach ($imgs as $key => &$_img) {
            $imgsData[] = new OmGoodsImg($_img);
        }
        $goods->imgs()->saveMany($imgsData);
    }
}