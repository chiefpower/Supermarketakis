document.addEventListener("DOMContentLoaded", function () {

fetch('https://restcountries.com/v3.1/all')
                      .then(response => response.json())
                      .then(countries => {
                        const countrySelect = document.getElementById('country');
                        countries.forEach(country => {
                          const option = document.createElement('option');
                          option.value = country.cca2;  // Use country code (e.g., US, IN, GB)
                          option.textContent = country.name.common;  // Use the country's common name
                          countrySelect.appendChild(option);
                        });
                      })
                      .catch(error => {
                        console.error('Error fetching countries:', error);
                      });
});