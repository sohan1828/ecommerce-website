<?php

// Renders a 5-star rating display (font-awesome stars) plus review count.
function render_stars($rating, $count){
   $rating = (float) $rating;
   $count = (int) $count;
   $html = '<div class="stars">';
   for($i = 1; $i <= 5; $i++){
      if($rating >= $i){
         $html .= '<i class="fas fa-star"></i>';
      }elseif($rating >= $i - 0.5){
         $html .= '<i class="fas fa-star-half-alt"></i>';
      }else{
         $html .= '<i class="far fa-star"></i>';
      }
   }
   $html .= ' <span>'.number_format($rating, 1).' ('.$count.')</span>';
   $html .= '</div>';
   return $html;
}

// Renders the price block, showing a struck-through original price and a
// discount badge when the product has a discount percentage set.
function render_price($price, $discount){
   $price = (float) $price;
   $discount = (int) $discount;

   if($discount > 0){
      $discounted = round($price - ($price * $discount / 100));
      return '
         <div class="price">
            <span>Rs.</span>'.$discounted.'<span>/-</span>
            <span class="old-price">Rs.'.$price.'</span>
         </div>
      ';
   }

   return '<div class="price"><span>Rs.</span>'.$price.'<span>/-</span></div>';
}

// Renders the discount corner badge, empty string if no discount.
function render_discount_badge($discount){
   $discount = (int) $discount;
   if($discount <= 0) return '';
   return '<span class="discount-badge">-'.$discount.'%</span>';
}

// Renders a stock indicator strip. Returns markup plus whether add-to-cart
// should be disabled, so callers can gate the submit button.
function render_stock_note($stock){
   $stock = (int) $stock;
   if($stock <= 0){
      return '<div class="stock-note out">out of stock</div>';
   }elseif($stock <= 5){
      return '<div class="stock-note low">only '.$stock.' left</div>';
   }
   return '';
}