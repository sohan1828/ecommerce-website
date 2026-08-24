let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');
let menuBtn = document.querySelector('#menu-btn');
let userBtn = document.querySelector('#user-btn');

menuBtn.onclick = () =>{
   let isActive = navbar.classList.toggle('active');
   menuBtn.setAttribute('aria-expanded', isActive);
   profile.classList.remove('active');
   userBtn.setAttribute('aria-expanded', 'false');
}

userBtn.onclick = () =>{
   let isActive = profile.classList.toggle('active');
   userBtn.setAttribute('aria-expanded', isActive);
   navbar.classList.remove('active');
   menuBtn.setAttribute('aria-expanded', 'false');
}

window.onscroll = () =>{
   navbar.classList.remove('active');
   profile.classList.remove('active');
   menuBtn.setAttribute('aria-expanded', 'false');
   userBtn.setAttribute('aria-expanded', 'false');
}



let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');
let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');

subImages.forEach(images =>{
   images.onclick = () =>{
      src = images.getAttribute('src');
      mainImage.src = src;
   }
});


// Initialize Swiper sliders
if(document.querySelector(".home-slider")){
   var homeSwiper = new Swiper(".home-slider", {
      loop: true,
      spaceBetween: 20,
      grabCursor:true,
      effect: "flip",
      pagination: {
          el: ".swiper-pagination",
          clickable: true,
      },
   });
}

if(document.querySelector(".category-slider")){
   var categorySwiper = new Swiper(".category-slider", {
      loop: true,
      spaceBetween: 20,
      grabCursor:true,
      
      pagination: {
          el: ".swiper-pagination",
          clickable: true,
      },
      breakpoints: {
          0: {
              slidesPerView: 2,
          },
          650: {
              slidesPerView: 3,
          },
          768: {
              slidesPerView: 4,
          },
          1024: {
              slidesPerView: 5,
          },
      },
   });
}

if(document.querySelector(".products-slider")){
   var productsSwiper = new Swiper(".products-slider", {
      loop: true,
      grabCursor:true,
      spaceBetween: 20,
      pagination: {
          el: ".swiper-pagination",
          clickable: true,
      },
      breakpoints: {
          550: {
              slidesPerView: 2,
          },
          768: {
              slidesPerView: 2,
          },
          1024: {
              slidesPerView: 3,
          },
      },

   });
}

if(document.querySelector(".deals-slider")){
   var dealsSwiper = new Swiper(".deals-slider", {
      loop: true,
      grabCursor:true,
      spaceBetween: 20,
      pagination: {
          el: ".swiper-pagination",
          clickable: true,
      },
      breakpoints: {
          550: {
              slidesPerView: 2,
          },
          768: {
              slidesPerView: 2,
          },
          1024: {
              slidesPerView: 3,
          },
      },

   });
}


let lastCheckTime = Math.floor(Date.now() / 1000); 

function checkForNewProducts() {
    // Call our new PHP endpoint to check for new products since the last check
    fetch(`fetch_new_products.php?last_check=${lastCheckTime}`)
        .then(response => response.json())
        .then(data => {
            // Update our timestamp for the next check
            lastCheckTime = data.timestamp;

            // If there are new products, add them to the page
            if (data.products.length > 0) {
                renderNewProducts(data.products);
            }
        })
        .catch(error => console.error('Error fetching products:', error));
}


setInterval(checkForNewProducts, 10000);

// --- Auto-refresh: poll for newly added products every 10s ---

function escapeHtml(str){
   const div = document.createElement('div');
   div.textContent = str == null ? '' : String(str);
   return div.innerHTML;
}

function buildProductCardHtml(product, wrapTag){
   const discount = parseInt(product.discount, 10) || 0;
   const stock = parseInt(product.stock, 10) || 0;
   const price = parseInt(product.price, 10) || 0;
   const rating = parseFloat(product.rating) || 0;
   const ratingCount = parseInt(product.rating_count, 10) || 0;

   let starsHtml = '<div class="stars">';
   for(let i = 1; i <= 5; i++){
      if(rating >= i){ starsHtml += '<i class="fas fa-star"></i>'; }
      else if(rating >= i - 0.5){ starsHtml += '<i class="fas fa-star-half-alt"></i>'; }
      else{ starsHtml += '<i class="far fa-star"></i>'; }
   }
   starsHtml += ' <span>'+rating.toFixed(1)+' ('+ratingCount+')</span></div>';

   let priceHtml;
   if(discount > 0){
      const discounted = Math.round(price - (price * discount / 100));
      priceHtml = '<div class="price"><span>Rs.</span>'+discounted+'<span>/-</span><span class="old-price">Rs.'+price+'</span></div>';
   }else{
      priceHtml = '<div class="price"><span>Rs.</span>'+price+'<span>/-</span></div>';
   }

   let stockHtml = '';
   if(stock <= 0){ stockHtml = '<div class="stock-note out">out of stock</div>'; }
   else if(stock <= 5){ stockHtml = '<div class="stock-note low">only '+stock+' left</div>'; }

   const badgeHtml = discount > 0 ? '<span class="discount-badge">-'+discount+'%</span>' : '';

   return `
      <input type="hidden" name="pid" value="${escapeHtml(product.id)}">
      <input type="hidden" name="name" value="${escapeHtml(product.name)}">
      <input type="hidden" name="price" value="${escapeHtml(product.price)}">
      <input type="hidden" name="image" value="${escapeHtml(product.image_01)}">
      ${badgeHtml}
      <button type="submit" class="fas fa-heart" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=${escapeHtml(product.id)}" class="fas fa-eye"></a>
      <img src="uploaded_img/${escapeHtml(product.image_01)}" alt="${escapeHtml(product.name)}">
      <div class="name">${escapeHtml(product.name)}</div>
      ${starsHtml}
      ${stockHtml}
      <div class="flex">
         ${priceHtml}
         <input type="number" name="qty" class="qty" min="1" max="${stock > 0 ? stock : 99}" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart" ${stock <= 0 ? 'disabled' : ''}>
   `;
}

function renderNewProducts(products){
   // Home page: products live inside a Swiper carousel (.products-slider).
   // Raw DOM insertion desyncs a looped Swiper's internal slide tracking,
   // so new slides are added through Swiper's own API instead.
   if(typeof productsSwiper !== 'undefined' && productsSwiper){
      const slidesHtml = products.map(product => {
         const form = document.createElement('form');
         form.className = 'swiper-slide slide';
         form.method = 'post';
         form.innerHTML = buildProductCardHtml(product);
         return form.outerHTML;
      });
      productsSwiper.prependSlide(slidesHtml);
      if(productsSwiper.params.loop){
         productsSwiper.loopDestroy();
         productsSwiper.loopCreate();
      }
      productsSwiper.update();
      // Newly injected markup needs its qty input wrapped with +/- buttons too.
      document.querySelectorAll('.products-slider input.qty').forEach(attachStepper);
      return;
   }

   // Shop page: plain grid, safe to insert directly.
   const container = document.querySelector('.products .box-container');
   if(!container) return;

   products.forEach(product => {
      const box = document.createElement('form');
      box.className = 'box';
      box.method = 'post';
      box.innerHTML = buildProductCardHtml(product);
      container.prepend(box);
      attachStepper(box.querySelector('input.qty'));
   });
}

// Function to attach stepper buttons to a given input
function attachStepper(input) {
    if(!input || input.closest('.qty-stepper')) return; 

    var wrapper = document.createElement('div');
    wrapper.className = 'qty-stepper';
    input.parentNode.insertBefore(wrapper, input);

    var minus = document.createElement('button');
    minus.type = 'button';
    minus.className = 'qty-minus';
    minus.textContent = '−';

    var plus = document.createElement('button');
    plus.type = 'button';
    plus.className = 'qty-plus';
    plus.textContent = '+';

    wrapper.appendChild(minus);
    wrapper.appendChild(input);
    wrapper.appendChild(plus);

    minus.addEventListener('click', function(){
        var min = parseInt(input.min, 10) || 1;
        var val = parseInt(input.value, 10) || min;
        if(val > min) input.value = val - 1;
    });

    plus.addEventListener('click', function(){
        var max = parseInt(input.max, 10) || 99;
        var val = parseInt(input.value, 10) || 1;
        if(val < max) input.value = val + 1;
    });
}

// Works everywhere <input class="qty"> appears (home, shop, cart,
// wishlist, quick view, search) without editing each page's markup.
document.querySelectorAll('input.qty').forEach(attachStepper);