$(document).ready(function() {

    document.querySelector('a[href="#fruits"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=fruit')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });

    document.querySelector('a[href="#drinks"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=drinks')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });

     document.querySelector('a[href="#meat"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=meat')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });

    document.querySelector('a[href="#frozenfood"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=frozen food')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });

    document.querySelector('a[href="#household"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=household')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });

     document.querySelector('a[href="#personalcare"]').addEventListener('click', function(e) {
     e.preventDefault();

     fetch('get_products.php?category=personal care')
     .then(response => response.text())
      .then(html => {
          document.querySelector('.content-area').innerHTML = html;
       })
       .catch(error => console.error('Failed to load products:', error));
    });
});