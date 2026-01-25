<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart_contents = Cart::content()->filter(fn($item) => $item->options->type == 'sale');
        $stockIds = $cart_contents->pluck('options.stock_id')->filter()->unique();
        $batchNos = Stock::whereIn('id', $stockIds)->pluck('batch_no', 'id');
        foreach ($cart_contents as $cartItem) {
            $stockId = $cartItem->options->stock_id ?? null;
            if ($stockId && isset($batchNos[$stockId])) {
                $newOptions = $cartItem->options->merge([
                    'batch_no' => $batchNos[$stockId],
                ]);
                Cart::update($cartItem->rowId, [
                    'qty' => $cartItem->qty,
                    'options' => $newOptions,
                ]);
            }
        }
        return view('business::sales.cart-list', compact('cart_contents'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'nullable|string|in:sale,purchase',
            'id' => 'required|integer',
            'stock_id' => 'nullable|exists:stocks,id',
            'name' => 'required|string',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'product_code' => 'nullable|string',
            'product_unit_id' => 'nullable|integer',
            'product_unit_name' => 'nullable|string',
            'product_image' => 'nullable|string',
            'sales_price' => 'nullable|numeric',
            'whole_sale_price' => 'nullable|numeric',
            'dealer_price' => 'nullable|numeric',
            'expire_date' => 'nullable|date',
        ]);

        // Check for existing item in cart by type
        $existingCartItem = Cart::search(
            fn($item) => $item->id == $request->id &&
                $item->options->type == $request->type &&
                match ($request->type) {
                    'purchase' => $item->options->batch_no == $request->batch_no,
                    'sale' => $item->options->stock_id == $request->stock_id,
                    default => false,
                }
        )->first();

        if ($existingCartItem) {
            $newQty = $existingCartItem->qty + $request->quantity;
            Cart::update($existingCartItem->rowId, ['qty' => $newQty]);
        } else {
            // Add new item to cart
            $mainItemData = [
                'id' => $request->id,
                'name' => $request->name,
                'qty' => $request->quantity,
                'price' => $request->price,
                'options' => [
                    'type' => $request->type,
                    'product_code' => $request->product_code,
                    'product_unit_id' => $request->product_unit_id,
                    'product_unit_name' => $request->product_unit_name,
                    'stock_id' => $request->stock_id,
                    'batch_no' => $request->batch_no,
                    'product_image' => $request->product_image,
                    'expire_date' => $request->expire_date,
                    'purchase_price' => $request->purchase_price,
                    'sales_price' => $request->sales_price,
                    'whole_sale_price' => $request->whole_sale_price,
                    'dealer_price' => $request->dealer_price,
                ]
            ];
            Cart::add($mainItemData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        try {

            $cart = Cart::get($id);

            if ($cart) {
                $quantity = $request->input('');
                $price = $request->input(''); // If sale

                if ($quantity >= 0) {
                    $updateData = ['qty' => $quantity];

                    if ($price !== null && $price >= 0) {
                        $updateData['price'] = $price;
                    }

                    // Update the cart
                    Cart::update($id, [
                        'qty' => $request->qty ?? $cart->qty,
                        'price' => $request->price ?? $cart->price,
                        'options' => [
                            'type' => $cart->options->type,
                            'expire_date' => $request->expire_date ?? $cart->options->expire_date,
                            'stock_id' => $request->stock_id ?? $cart->options->stock_id,
                            'batch_no' => $request->batch_no ?? $cart->options->batch_no,
                            'product_code' => $cart->options->product_code,
                            'product_unit_id' => $cart->options->product_unit_id,
                            'product_unit_name' => $cart->options->product_unit_name,
                            'product_image' => $cart->options->product_image,
                            'sales_price' => $cart->options->sales_price,
                            'whole_sale_price' => $cart->options->whole_sale_price,
                            'dealer_price' => $cart->options->dealer_price,
                            'purchase_price' => $cart->options->purchase_price,
                        ]
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => __('Quantity') .
                            ($price !== null ? __(' and price') : '') .
                            __(' updated successfully')
                    ]);
                } else {
                    return response()->json(['success' => false, 'message' => __('Enter a valid quantity')]);
                }
            } else {
                return response()->json(['success' => false, 'message' => __('Item not found in the cart')]);
            }
        } catch (InvalidRowIDException $e) {
            return response()->json(['success' => false, 'message' => __('The cart does not contain this item')]);
        }
    }

    public function destroy($id)
    {
        try {
            Cart::remove($id);
            return response()->json(['success' => true, 'message' => __('Item removed from cart')]);
        } catch (InvalidRowIDException $e) {
            return response()->json(['success' => false, 'message' => __('The cart does not contain this item')]);
        }
    }

    public function removeAllCart(Request $request)
    {
        $carts = Cart::content();

        if ($carts->count() < 1) {
            return response()->json(['message' => __('Cart is empty. Add items first!')]);
        }

        Cart::destroy();

        $response = [
            'success' => true,
            'message' => __('All cart removed successfully!'),
        ];

        return response()->json($response);
    }
}
