<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Aspose\Words\Model\Requests\ConvertDocumentRequest;
use Aspose\Words\WordsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function get_types(Request $request)
    {
        $types = DocumentType::all();
        return response()->json([
            'data' => $types,
        ]);
    }

    public function get_count(Request $request)
    {
        $user = auth()->user();
        $count = DB::table('documents')
            ->where('user_id', $user->id)
            ->count('user_id');
        return response()->json([
            'data' => $count,
        ]);
    }

    public function search_property(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search') ?? '';

        $user = auth()->user();

        $items = DB::table('documents')
            ->select($type, DB::raw('MAX(updated_at) as updated'))
            ->where('user_id', $user->id)
            ->where($type, 'like', '%' . $search . '%')
            ->where($type, '!=', '')
            ->groupBy($type)
            ->orderBy('updated', 'desc')
            ->pluck($type)
            ->take(3)
            ->toArray();
       // echo $items;

        return response()->json([
            'data' => $items,
        ]);
        /*//[0818-6)-step 2]
        if($type == 'category'){
            $categorys[] = 'My Documents';
        }else if($type == 'type'){
            $types[] = 'NDA';
            $types[] = 'MSA';
            $types[] = 'SOW';
        }    
        if($type == 'category'){
            if(count($items) <= 1){
                $items = array_merge($categorys, $items);
            }
        }
        $uniqueArray = array_unique($items);
        $uniqueArray = array_values($uniqueArray);            
        return response()->json([
            'data' => $uniqueArray,
        ]);*/
    }

    public function select_property(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search') ?? '';

        $user = auth()->user();

        $items = DB::table('documents')
            ->select($type)
            ->where('user_id', $user->id)
        //    ->where($type, 'like', '%' . $search . '%')
            ->distinct()
            ->orderBy($type, 'desc')
            ->limit(3)
            ->pluck($type)
            ->toArray();

        if($type == 'category'){
            $categorys[] = 'My Documents';
        }else if($type == 'type'){
            $types[] = 'NDA';
            $types[] = 'MSA';
            $types[] = 'SOW';
        }    
        if($type == 'category'){
            $items = array_merge($categorys, $items);
        }else if($type == 'type'){
            $items = array_merge($items, $types);
        }
        $uniqueArray = array_unique($items);
        $uniqueArray = array_values($uniqueArray);            
        return response()->json([
            'data' => $uniqueArray,
        ]);
    }

    public function document_convert(Request $request)
    {
        $file = $request->file('document');
        $document = $file->store('public/documents');
        $requestDocument = storage_path('app/' . $document);
        if (in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            // $path = $file->getRealPath();
            // $content = \PhpOffice\PhpWord\IOFactory::load($path);
            // $writer = \PhpOffice\PhpWord\IOFactory::createWriter($content, 'HTML');

            $wordsApi = new WordsApi('7d84cf75-6114-43b8-8d26-78576e8c12fc', '5d13151d1fa996827164258ef8fb6151');

            $request = new ConvertDocumentRequest(
                $requestDocument, "html", null, null, null, null
            );
            $content = $wordsApi->convertDocument($request);
            $htmlContent = $content->getPathName();
            $htmlContent = file_get_contents($htmlContent);

            // ob_start();
            // $writer->save("php://output");
            // $content = ob_get_contents();
            // ob_end_clean();

            return response()->json([
                'data' => $htmlContent,
            ]);
        }

        return response()->json([
            'data' => file_get_contents($file->getRealPath()),
        ]);
    }
}
