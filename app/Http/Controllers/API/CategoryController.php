<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Validator;
use Storage;
use DB;
class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['index']]);
    }
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             default="1"
     *         ),
     *         description="Page number default 1"
     *     ),
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    public function index()
    {
//        return response()->json(Category::all());
        $list = Category::paginate(100);
        return response()->json($list,200);
    }



    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category",
     *   security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo", "name", "description"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    public function store(Request $request)
    {
        $input =$request->all();
        // validate all
        $msgs = array(
            'name.required' => 'Enter category name',
            'description.required' => 'Enter description',
        );
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'photo' => 'required',
        ], $msgs);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        // validation pass
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            // Generate a unique filename
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];
            foreach ($sizes as $size)
            {
                $fileSave = $size.'_'.$filename;
                // Resize the image while maintaining aspect ratio
                $resizedImage = Image::make($image)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                // Save the resized image
                $path = public_path('uploads/category/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['photo'] = $filename;
        }
        $category = Category::create($input);
        return response()->json($input);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

     /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          description="Category id to get",
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
        $category = DB::table('categories')->find($id);
        if($category == null)
            return response()->json("Not found", 404);

        return response()->json(['request'=>'get by id','result'=>$category]);
    }


    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category/{id}",
     *   security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *          name="id",
     *          description="Category id to edit",
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
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
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
        $messages = array(
            'photo.image' => 'This file must be image type!',
            'photo.max' => 'This size of this image must be less than 5MB!',
        );
        $validator = Validator::make($input, [
            'photo' => 'image|max:5000',
        ], $messages);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        // take old value from database
        $category = DB::table('categories')->find($id);
        // if user request to edit image
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            // Generate a unique filename
            $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];
            //remove old images
            foreach ($sizes as $size) {
                $fileDelete = $size.'_'.$category->photo;
                $removePath = public_path('uploads/category/' . $fileDelete);
                if (file_exists($removePath)) {
                    unlink($removePath);
                }
            }

            foreach ($sizes as $size)
            {
                $fileSave = $size.'_'.$filename;
                // Resize the image while maintaining aspect ratio
                $resizedImage = Image::make($photo)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                // Save the resized image
                $path = public_path('uploads/category/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['photo'] = $filename;
        }
        else {
            $input['photo'] = $category->photo;
        }

        $category = Category::find($id);
        $category->update($input);

        return response()->json(['request'=>'edit by id','result'=>$category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['success' => true]);
    }
}
