<style>
    #editableDiv tr td{
        height: 10px !important;
    }
    #editableDiv tr{
        height: 10px !important;
    }
</style>

<div contenteditable="true" id="editableDiv" style="border: 1px solid #000; border-radius:10px; padding: 10px; width: 100%; min-height: 50px;">

</div>


<script>

    const editableDiv = document.getElementById("editableDiv");

    editableDiv.addEventListener('paste', () => {
        setTimeout(() => {
            const table = editableDiv.querySelector('table');
            if (table) {
                const jsonResult = convertTableToJSON(table);
                console.log(jsonResult);
                const reversedJsonResult = jsonResult.reverse();

                fetch('/api/treasury-logs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(reversedJsonResult), // Отправляем JSON
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                        location.reload(); // Обновляет страницу
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        }, 0); // Небольшая задержка для обработки вставки
    });


    function convertTableToJSON(table) {
        const rows = table.querySelectorAll('tr');
        const result = [];

        // Функция для преобразования количества монет в золотые монеты
        const convertToGold = (quantity, coinType) => {
            if (!coinType) {
                // Если нет изображения, считаем, что это золотая монета
                return quantity;
            }
            switch (coinType) {
                case 'm_game2.gif': // серебряная монета
                    return quantity * 0.01;
                case 'm_game1.gif': // медная монета
                    return quantity * 0.0001;
                case 'm_game3.gif': // золотая монета
                default:
                    return quantity; // уже в золотых монетах
            }
        };

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const clan_id = {{ $clan_id }};
                const date = cells[0].textContent.trim();
                const name = cells[1].textContent.trim();
                const type = cells[2].textContent.trim();
                const object = cells[3].textContent.trim();
                const quantityCell = cells[4];

                // Находим все изображения монет в ячейке
                const images = quantityCell.querySelectorAll('img');

                // Извлекаем количество монет
                let quantities = quantityCell.textContent.trim().split(/\s+/).filter(word => !isNaN(word)).map(Number);

                let totalGoldQuantity = 0;

                if (images.length === quantities.length) {
                    // Если изображений и чисел одинаковое количество
                    images.forEach((img, index) => {
                        const coinType = img.src.split('/').pop(); // получаем тип монеты из пути изображения
                        const quantity = quantities[index];
                        totalGoldQuantity += convertToGold(quantity, coinType); // суммируем конвертированное количество
                    });
                } else {
                    // Если изображений больше, чем чисел, или наоборот, предполагаем, что количество одно для всех
                    const coinType = images[0] ? images[0].src.split('/').pop() : ''; // тип монеты из первого изображения
                    const quantity = quantities[0] || 0;
                    totalGoldQuantity = convertToGold(quantity, coinType);
                }

                // Если текст в ячейке начинается с минуса, делаем totalGoldQuantity отрицательным
                if (quantityCell.textContent.trim().startsWith('-')) {
                    totalGoldQuantity = -Math.abs(totalGoldQuantity);
                }

                // Добавляем строку с конвертированным количеством монет
                result.push({
                    clan_id: clan_id,
                    Date: date,
                    Name: name,
                    Type: type,
                    Object: object,
                    Quantity: totalGoldQuantity.toFixed(4)
                });
            }
        });

        return result;
    }

</script>