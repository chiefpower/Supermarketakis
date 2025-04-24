document.addEventListener('DOMContentLoaded', function () {
    fetch('get_stores.php')
      .then(response => response.json())
      .then(stores => {
        const select = document.getElementById('preferredStore');
        stores.forEach(store => {
          const option = document.createElement('option');
          option.value = store.id;
          option.textContent = store.name;
          select.appendChild(option);
        });
      })
      .catch(error => {
        console.error('Error fetching stores:', error);
      });
});

