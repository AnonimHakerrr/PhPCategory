<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Validator;
use Storage;
use DB;
class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products",
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    public function index()
    {

        $list = Product::all();
        return response()->json($list,200);
    }


    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Product"},
     *     summary="Create a new product",
     *     description="Creates a new product with the provided data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     format="double"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 required={
     *                     "category_id",
     *                     "name",
     *                     "price",
     *                     "description"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request. Invalid or missing parameters"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */

    public function store(Request $request)
    {
        // validate all
        $msgs = array(
            'name.required' => 'Enter category name',
            'description.required' => 'Enter description',
        );
        $inputs=$request->all();
        $validator = Validator::make($inputs, [
            'category_id'=>'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
        ], $msgs);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $product = Product::create($inputs);
        return response()->json($inputs,200);
    }


    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          description="Product id to get",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(response="200", description="One category found."),
     *     @OA\Response(response="404", description="Category not found"),
     *     @OA\Response(response="400", description="Request validation fault"),
     * )
     */
    public function edit(string $id)
    {
        $products = DB::table('products')->find($id);
        if($products == null)
            return response()->json("Not found", 404);

        return response()->json(['request'=>'get by id','result'=>$products]);
    }



    /**
     * @OA\Post(
     *     tags={"Product"},
     *     path="/api/products/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          description="Product id to edit",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                @OA\Property(
     *                     property="category_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     format="double"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="400", description="Validation has fault"),
     * )
     */
    public function update(Request $request, string $id)
    {
        $input = $request->all();



        // take old value from database
        $products = DB::table('products')->find($id);
        // if user request to edit image


        $products = Product::find($id);
        $products->update($input);

        return response()->json(['request'=>'edit by id','result'=>$products]);
    }






    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Product"},
     *     summary="Delete a Product",
     *     description="Deletes a specific Product based on the provided ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Product to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy(string  $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['success' => true]);
    }
}
