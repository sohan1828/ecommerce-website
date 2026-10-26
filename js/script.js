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


setInterval(checkForNewProducts, 5000);

function renderNewProducts(products) {
    const container = document.querySelector('.products .box-container');
    
    if(!container) return; 

    products.forEach(product => {
        
        const box = document.createElement('form');
        box.className = 'box';
        box.action = ''; 
        box.method = 'post';

        
        box.innerHTML = `
            <input type="hidden" name="pid" value="${product.id}">
            <input type="hidden" name="name" value="${product.name}">
            <input type="hidden" name="price" value="${product.price}">
            <input type="hidden" name="image" value="${product.image_01}">
            
            <button type="submit" class="fas fa-heart" name="add_to_wishlist"></button>
            <a href="quick_view.php?pid=${product.id}" class="fas fa-eye"></a>
            
            <img src="uploaded_img/${product.image_01}" alt="">
            <div class="name">${product.name}</div>
            <div class="flex">
                <div class="price"><span>$</span>${product.price}<span>/-</span></div>
                <input type="number" name="qty" class="qty" min="1" max="${product.stock}" value="1">
            </div>
            <input type="submit" value="add to cart" class="btn" name="add_to_cart">
        `;

        
        container.prepend(box); 
    });
}

// Function to attach stepper buttons to a given input
function attachStepper(input) {
    if(input.closest('.qty-stepper')) return; 

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
document.querySelectorAll('input.qty').forEach(function(input){
   if(input.closest('.qty-stepper')) return;

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
});
