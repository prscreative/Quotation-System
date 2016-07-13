<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Products;//Products model.
use App\Customers;//Customers model to fetch customers.
use App\Inventory;//Inventory model.
use App\Quotations;//Quotations model.
use App\QuoteItems;//QuoteItems model.
use Auth;//for authentication!

class QuoteController extends Controller
{
    // Navbar links for add new quote.
    // Text = anchor text, Link = really?, Icon = fa icon name.
    private static $Links = [
    ['text'=>'Save Quote','link'=>"",'icon'=>'check'],
    ['text'=>'Email','link'=>"",'icon'=>'envelope'],
    ['text'=>'Print','link'=>"",'icon'=>'print'],
    ['text'=>'Cancel','link'=>"",'icon'=>'ban']
    ];

    public function __construct()
    {
        $this->middleware('auth');
        QuoteController::$Links[0]['link'] = QuoteController::$Links[3]['link'] = url('quotations');
    }

    // Show all quatations
    public function index()
    {
        //get all quotations with their info
        $quotes = Quotations::select('quotations.id')->join('customers','customers.id','=','quotations.customer_id')
                            ->addSelect('quotations.expiry_date')
                            ->addSelect('quotations.payment_term')
                            ->addSelect('customers.company_name')
                            ->addSelect('customers.customer_name')->get();
        return view('index_views/quotes',['title' => 'Quotations','quotes'=>$quotes]);
    }

	// Show add quatation.
    public function create()
    {
        $customers = Customers::where('customer_vendor','customer')->get();
        $products = Products::all();//need to get only items that has stocks.
        return view('create_views/new_quote',['title' => 'New Quotation', 
                    'nav_links'=>QuoteController::$Links,
                    'customers'=>$customers, 'products'=>$products, 'quote'=> new Quotations]);
    }    

    // Add quote.
    public function store(Request $req)
    {
        //Insert product info to db through products model.
        $quote = Quotations::Create([
            'customer_id' => $req['customer_id'],
            'expiry_date' => $req['expiry_date'],
            'status' => 'Quotation', 
            'payment_term' => $req['payment_term'],
            'added_by' => Auth::user()->id
        ]);
        echo $quote->id;
    } 

    // Show Edit quotation form.
    public function edit(Request $session, $quote_id)
    {
        try
        {
            //Find the customer object from model.
            $quote = Quotations::findOrFail($quote_id);
            //Redirect to edit quotation form with the customer info found above.
            $customers = Customers::where('customer_vendor','customer')->get();
            $products = Products::all();//need to get only items that has stocks.
            return view('create_views/new_quote',['title' => 'New Quotation', 
                        'nav_links'=>QuoteController::$Links,
                        'customers'=>$customers, 'products'=>$products,
                        'quote'=>$quote
                        ]);
        }
        catch(ModelNotFoundException $err){
            //Show error message
            $session->session()->flash('flash_msg', "Product Doesn't Exist");
            //Redirect to products page.
            return $this->index();
        }
    }   
}