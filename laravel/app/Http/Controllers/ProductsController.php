<?php

namespace App\Http\Controllers;

use App\Models\Product as ModelsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Facades\Storage as FacadesStorage;

class ProductsController extends Controller
{
    /**
     * Create a new ProductsController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return responder()->success(['products' => ModelsProduct::all()])->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $messages = [
            'name.required' => 'O campo Nome é obrigatório.',
            'price.required' => 'O campo Preço é obrigatório.',
            'price.integer' => 'O campo Preço deve ser inteiro.',
            'quantity.required' => 'O campo Quantidade é obrigatório.',
            'quantity.integer' => 'O campo Quantidade deve ser inteiro.',
            'image.required' => 'O campo Imagem é obrigatório.',
            'image.mimes' => 'O campo Imagem deve ser do tipo [jpeg,png].',
        ];
        $validator = FacadesValidator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'image' => 'required|mimes:jpeg,png',
        ], $messages);

        if( $validator->fails() ){
            $aux = array();
            if( $validator->errors()->has('name') ){
                array_push($aux, ['fieldname' => 'name', 'message' => $validator->errors()->get('name')[0]]);
            }

            if( $validator->errors()->has('price') ){
                array_push($aux, ['fieldname' => 'price', 'message' => $validator->errors()->get('price')[0]]);
            }

            if( $validator->errors()->has('quantity') ){
                array_push($aux, ['fieldname' => 'quantity', 'message' => $validator->errors()->get('quantity')[0]]);
            }

            if( $validator->errors()->has('image') ){
                array_push($aux, ['fieldname' => 'image', 'message' => $validator->errors()->get('image')[0]]);
            }

            return responder()->error(422, 'Ocorreu um erro de validação')->data(['errors' => $aux])->respond(422);
        }

        $image = $request->image->store('public/images');

        $product = new ModelsProduct();
        $product->name      = $request->name;
        $product->price     = $request->price;
        $product->quantity  = $request->quantity;
        $product->image     = $image;

        if( ! $product->save() ){
            return responder()->error(422, 'Erro ao cadastrar produto.')->respond(422);
        }

        return responder()->success(['product' => $product])->respond(201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $product = ModelsProduct::find($id);
        if( $product ){
            return responder()->success(['product' => $product])->respond();
        }

        return responder()->error(422, 'Produto não encontrado.')->respond(422);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $product = ModelsProduct::find($id);
    
        if( ! $product ){
            return responder()->error(422, 'Produto não encontrado.')->respond(422);
        }
        
        $updatedProduct = $request->all();
        if( $request->image ){
            FacadesStorage::delete($product['image']);
            $updatedProduct['image'] = $request->image->store('public/images');
        }
        
        unset($updatedProduct['_method']);
        if( ! $product->fill($updatedProduct)->save() ){
            return responder()->error(422, 'Erro ao salvar produto.')->respond(422);
        }

        return responder()->success(['product' => $updatedProduct])->respond();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $product = ModelsProduct::find($id);
    
        if( ! $product ){
            return responder()->error(422, 'Produto não encontrado.')->respond(422);
        }

        if( ! $product->delete() ){
            return responder()->error(422, 'Erro ao deletar produto.')->respond(422);
        }

        FacadesStorage::delete($product['image']);
        return responder()->success()->meta(['message' => 'Produto deletado com sucesso!'])->respond();

    }

}
