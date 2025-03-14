<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Accommodation;
use App\Models\Category;
use App\Models\Booking;
use App\Models\EcoItem;
use App\Models\Hashtag;
use App\Models\Review;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class AccommodationController extends Controller
{

    // master push
    private $accommodation;
    private $category;
    private $hashtag;
    private $ecoitem;

    private $booking;

    public function __construct(Accommodation $accommodation, Category $category, Hashtag $hashtag, EcoItem $ecoitem)
    {
        $this->accommodation = $accommodation;
        $this->category      = $category;
        $this->hashtag       = $hashtag;
        $this->ecoitem       = $ecoitem;
    }



    public function create()
    {
        $all_categories = Category::all();
        $all_ecoitems = EcoItem::all();
        return view('accommodation.create')->with('all_categories', $all_categories)->with('all_ecoitems', $all_ecoitems);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string',
            'city'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'capacity'    => 'required|integer|min:1|max:100',
            'description' => 'required|string',
            'photos'      => 'required|array',
            'photos.*'    => 'image|mimes:jpeg,jpg,png,gif|max:1048',
            'ecoitem'     => 'nullable|array',
            'ecoitem.*'   => 'exists:ecoitems,id',
        ]);

        // リクエストから宿泊施設名と住所を取得
        $user_id = Auth::user()->id;
        $name = $validated['name'];
        $address = $validated['address'];
        $city = $validated['city'];
        $price = $validated['price'];
        $capacity = $validated['capacity'];
        $description = $validated['description'];
        $apiKey = config('services.google_maps.api_key');


        // try {
            // Google Geocoding APIを使用して緯度と経度、住所コンポーネントを取得
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey
            ]);

            $data = $response->json();

            if ($data['status'] == 'OK') {
                // 緯度・経度を取得
                $latitude = $data['results'][0]['geometry']['location']['lat'];
                $longitude = $data['results'][0]['geometry']['location']['lng'];

                $ecoitemIds = $request->ecoitem ?? [];
                $ecoitems = EcoItem::whereIn('id', $ecoitemIds)->get();
                $totalPoints = $ecoitems->sum('point');
                $ecoitems->count();
                $rank = 'C';
                // 平均ポイントの計算
                $sumPoints = $totalPoints;

                // ランクを決定
                if ($sumPoints >= 31) {
                    $rank = 'A';
                } elseif ($sumPoints >= 16) {
                    $rank = 'B';
                }

                $accommodation = Accommodation::create([
                    'user_id'     => $user_id,
                    'name'        => $name,
                    'address'     => $address,
                    'city'        => $city,
                    'price'       => $price,
                    'capacity'    => $capacity,
                    'description' => $description,
                    'rank'        => $rank,
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                ]);

                // タグのsection
                if (!empty($validated['description'])) {
                    // #タグを抽出
                    preg_match_all('/#(\w+)/', $validated['description'], $matches);
                    $tags = $matches[1];

                    // #タグを取り除いてdescriptionを更新
                    $descriptionWithoutTags = preg_replace('/#\w+/', '', $validated['description']);
                    $accommodation->description = trim($descriptionWithoutTags); // 前後の不要なスペースを削除
                    $accommodation->save();

                    $tagIds = [];
                    foreach ($tags as $tagName) {
                        if (!empty($tagName)) {
                            $tag = Hashtag::firstOrCreate(['name' => $tagName]);
                            $tagIds[] = $tag->id; // タグのIDを保存
                        }
                    }

                    if (!empty($tagIds)) {
                        $accommodation->hashtags()->attach($tagIds);
                    }
                }

                // 画像のsection
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $extension = $photo->getClientOriginalExtension();
                        $newFileName = Str::uuid() . '.' . $extension;

                        // S3 にアップロード
                        $path = Storage::disk('s3')->putFileAs('photos', $photo, $newFileName);

                        // S3 のフルURLを取得
                        $s3Url = Storage::disk('s3')->url($path);

                        // Photo モデルに保存
                        Photo::create([
                            'accommodation_id' => $accommodation->id,
                            'image' => $s3Url, // S3のURLを保存
                        ]);
                    }
                }


                // カテゴリーのsection
                $category_accommodation = [];
                if(is_array($request->category)) {
                    foreach ($request->category as $category_id) {
                        $category_accommodation[] = [
                            'category_id' => $category_id,
                            'accommodation_id' => $accommodation->id
                        ];
                    }
                }


                DB::table('category_accommodation')->insert($category_accommodation);


                // エコフレンドリーのsection
                $ecoitem_accommodation = [];
                if(is_array($request->ecoitem)) {
                    foreach ($request->ecoitem as $ecoitem_id) {
                        $ecoitem_accommodation[] = [
                            'ecoitem_id' => $ecoitem_id,
                            'accommodation_id' => $accommodation->id
                        ];
                    }
                }


                DB::table('ecoitem_accommodation')->insert($ecoitem_accommodation);

                return redirect()->route('accommodation.show', $accommodation->id)
                    ->with('success', '宿泊施設が登録されました');
            } else {
                return redirect()->route('host.accommodation.create')->with('googleMap_Error', 'Something went wrong with the address.');
            }
    }

    public function edit($id)
    {
        $accommodation = $this->accommodation->findOrFail($id);


        if(Auth::user()->id != $accommodation->user->id){
            return redirect()->route('host.index');
        }

        $all_categories = $this->category->all();
        $all_ecoitems = $this->ecoitem->all();
        $all_hashtags = $this->hashtag->all();

        $selected_categories = [];
        foreach($accommodation->categoryAccommodation as $category_accommodation) {
            $selected_categories[] = $category_accommodation->category_id;
        }
        $selected_ecoitems = [];
        foreach($accommodation->ecoitemAccommodation as $ecoitem_accommodation) {
            $selected_ecoitems[] = $ecoitem_accommodation->ecoitem_id;
        }

        return view('accommodation.edit')
                ->with('accommodation', $accommodation)
                ->with('all_categories', $all_categories)
                ->with('selected_categories', $selected_categories)
                ->with('all_hashtags', $all_hashtags)
                ->with('all_ecoitems', $all_ecoitems)
                ->with('selected_ecoitems', $selected_ecoitems);

    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'capacity' => 'required|integer|min:1|max:100',
            'description' => 'required|string',
            'photos' => 'nullable|array|min:4',
            'photos.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:1048',
        ]);

            // 対象の宿泊施設を取得
            $accommodation = Accommodation::findOrFail($id);

            // リクエストから宿泊施設の情報を更新
            $user_id = Auth::user()->id;
            $name = $validated['name'];
            $address = $validated['address'];
            $city = $validated['city'];
            $price = $validated['price'];
            $capacity = $validated['capacity'];
            $description = $validated['description'];
            $apiKey = config('services.google_maps.api_key');

            // 住所が変更された場合、緯度・経度も更新
            if ($accommodation->address != $validated['address']) {
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $validated['address'],
                    'key' => $apiKey
                ]);
                $data = $response->json();
                if ($data['status'] == 'OK') {
                    $accommodation->latitude = $data['results'][0]['geometry']['location']['lat'];
                    $accommodation->longitude = $data['results'][0]['geometry']['location']['lng'];
                }
            }

            //ecoitems　section
            $ecoitem_accommodation = [];
                if(is_array($request->ecoitem)) {
                    foreach ($request->ecoitem as $ecoitem_id) {
                        $ecoitem_accommodation[] = [
                            'ecoitem_id' => $ecoitem_id,
                            'accommodation_id' => $accommodation->id
                        ];
                    }
                }

            // 既存のカテゴリ関連を同期（重複なし）
            $accommodation->ecoitems()->sync($ecoitem_accommodation);

            $ecoitemIds = $request->ecoitem ?? [];
                $ecoitems = EcoItem::whereIn('id', $ecoitemIds)->get();
                $totalPoints = $ecoitems->sum('point');
                $ecoitems->count();

                // 平均ポイントの計算
                $sumPoints = $totalPoints;
                $rank = 'C';

                // ランクを決定
                if ($sumPoints >= 31) {
                    $rank = 'A';
                } elseif ($sumPoints >= 16) {
                    $rank = 'B';
                }


            // 宿泊施設の情報を更新
            $accommodation->update([
                'user_id' => $user_id,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'price' => $price,
                'capacity' => $capacity,
                'description' => $description,
                'rank' => $rank,
                'latitude' => $accommodation->latitude,
                'longitude' => $accommodation->longitude,
            ]);

            // タグの更新処理
            if (!empty($validated['description'])) {
                // #タグを抽出
                preg_match_all('/#(\w+)/', $validated['description'], $matches);
                $tags = $matches[1];

                // #タグを取り除いてdescriptionを更新
                $descriptionWithoutTags = preg_replace('/#\w+/', '', $validated['description']);
                $accommodation->description = trim($descriptionWithoutTags); // 前後の不要なスペースを削除
                $accommodation->save();

                // タグを処理
                $tagIds = [];
                foreach ($tags as $tagName) {
                    // タグ名が空でないか確認
                    if (!empty($tagName)) {
                        // 'name' カラムを使ってタグを作成または取得
                        $tag = Hashtag::firstOrCreate(['name' => $tagName]);
                        $tagIds[] = $tag->id; // タグのIDを保存
                    }
                }
            }

            // 写真のアップロード処理
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    // UUIDを使ってファイル名を生成
                    $newFileName = Str::uuid() . '.' . $photo->getClientOriginalExtension();

                    // S3にアップロード
                    $path = Storage::disk('s3')->putFileAs('photos', $photo, $newFileName);

                    // S3のURLを取得
                    $s3Url = Storage::disk('s3')->url($path);

                    // Photoモデルで保存
                    Photo::create([
                        'accommodation_id' => $accommodation->id,
                        'image' => $s3Url, // S3のURLを保存
                    ]);
                }
            }


           // カテゴリの関連付け
            $category_accommodation = [];
            if(is_array($request->category)) {
                foreach ($request->category as $category_id) {
                    $category_accommodation[] = [
                        'category_id' => $category_id,
                        'accommodation_id' => $accommodation->id
                    ];
                }
            }

        // 既存のカテゴリ関連を同期（重複なし）
        $accommodation->categories()->sync($request->category ?? []);



            // 成功メッセージと共にリダイレクト
            return redirect()->route('accommodation.show', $accommodation->id)
                ->with('success', '宿泊施設が更新されました');
    }


    public function show($id)
    {
        $userId = Auth::id();
        $accommodation = Accommodation::with('photos')->findOrFail($id);

        $reviews = Review::where('accommodation_id', $id)->latest()->get();

        $latest_review = $reviews->first();
        $sumOfReview = $reviews->sum('star');
        $bookings = Booking::where('accommodation_id', $id)->where('guest_id', $userId)->get();

        $average = count($reviews) > 0 ? $sumOfReview / count($reviews) : 0;


        // ビューにデータを渡す
        return view('accommodation.show', compact('accommodation', 'reviews', 'latest_review', 'average', 'bookings'));
    }


    public function pictureIndex($id)
    {
        $accommodation = Accommodation::with('photos')->findOrFail($id);

        return view('accommodation.pictures', compact('accommodation'));
    }


    public function index()
    {
        $all_accommodations = $this->accommodation->where('user_id', Auth::user()->id)->latest()->paginate(3);

        return view('accommodation.index')->with('all_accommodations', $all_accommodations);
    }

    public function destroy($id)
{
    $accommodation = $this->accommodation->findOrFail($id);

    $photos = Photo::where('accommodation_id', $id)->get();

    foreach ($photos as $photo) {
        if (!empty($photo->image)) {
            // `parse_url()` でURLのパス部分だけ取得
            $path = ltrim(parse_url($photo->image, PHP_URL_PATH), '/');

            if (!empty($path)) {
                if (Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                } else {
                    \Log::warning("指定されたパスが存在しません: " . $path);
                }
            } else {
                \Log::warning("削除対象のパスが空です: " . $photo->image);
            }
        }
    }

    Photo::where('accommodation_id', $id)->delete();

    $accommodation->delete();

    return redirect()->route('host.index')->with('success', '宿泊施設を削除しました');
}


    public function search()
    {
        $accommodations = $this->accommodation->withCount('bookings')
                                            ->orderByDesc('bookings_count')
                                            ->take(6)
                                            ->get();

        $categories     =  $this->category->get();

        return view('accommodation.search')->with('all_accommodations', $accommodations)
                                                ->with('categories', $categories);
    }

    public function search_by_keyword(Request $request)
    {
        $categories     =  $this->category->get();
        $accommodations = $this->accommodation
                    ->where('address', 'LIKE', '%'. $request->keyword . '%')
                    ->orWhere('name', 'LIKE', '%'. $request->keyword . '%')
                    ->orWhere('city', 'LIKE', '%'. $request->keyword . '%')
                    ->orWhere('price', 'LIKE', '%'. $request->keyword . '%')
                    ->get();

        return view('accommodation.search')->with('all_accommodations', $accommodations)
                                                ->with('categories', $categories);
    }

    public function search_by_filters(Request $request)
    {
        $query  = $this->accommodation->query();

        $query->when($request->capacity, function ($q, $capacity) {
            $capacityRanges = [
                'capa_1' => [1, 2],
                'capa_2' => [3, 5],
                'capa_3' => [6, 10],
            ];

            if (isset($capacityRanges[$capacity])) {
                $q->whereBetween('capacity', $capacityRanges[$capacity]);
            } elseif ($capacity === 'capa_4') {
                $q->where('capacity', '>', 10);
            }
        });

        $query->when($request->filled(['min_price', 'max_price']), function ($q) use ($request) {
            $q->whereBetween('price', [$request->min_price, $request->max_price]);
        });

        $query->when($request->filled('city'), function ($q) use ($request) {
            $q->where('city', 'LIKE', '%' . $request->city . '%');
        });

        if ($request->has('category') && is_array($request->category)) {
            foreach ($request->category as $categoryId) {
                $query->whereHas('categories', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
        }

        $daterange = $request->input('daterange');

        if ($daterange) {
            $date = array_map('trim', explode(' - ', $daterange));
            if (count($date) == 2) {
                $starting_date = $date[0];
                $ending_date = $date[1];
            }
        }

        if ($starting_date && $ending_date) {
            $query->whereDoesntHave('bookings', function ($q) use ($starting_date, $ending_date) {
                $q->where(function ($query) use ($starting_date, $ending_date) {
                    $query->whereBetween('check_in_date', [$starting_date, $ending_date])
                        ->orWhereBetween('check_out_date', [$starting_date, $ending_date])
                        ->orWhere(function ($query) use ($starting_date, $ending_date) {
                            $query->where('check_in_date', '<=', $starting_date)
                                    ->where('check_out_date', '>=', $ending_date);
                    });
                });
            });
        }

        $accommodations = $query->get();
        $categories = $this->category->get();

        return view('accommodation.search')->with('all_accommodations', $accommodations)
                                            ->with('categories', $categories);
    }


}
