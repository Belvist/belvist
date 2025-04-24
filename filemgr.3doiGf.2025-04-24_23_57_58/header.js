console.log('header.js начал выполнение');

document.addEventListener('DOMContentLoaded', function () {
    console.log('header.js: DOMContentLoaded сработал');

    const accountLink = document.querySelector('#account-link span');

    // Проверяем, найден ли элемент
    if (!accountLink) {
        console.error('header.js: Элемент #account-link span не найден');
        // Все равно отправляем событие, чтобы другие страницы не зависли
        const event = new Event('authCheckComplete');
        document.dispatchEvent(event);
        return;
    }

    // Инициализируем глобальные переменные
    window.isAuthenticated = false;
    window.userId = null;
    console.log('header.js: Глобальные переменные инициализированы', { isAuthenticated: window.isAuthenticated, userId: window.userId });

    // Проверяем, авторизован ли пользователь
    fetch('/api/check_auth.php')
        .then(response => {
            console.log('header.js: Ответ от /api/check_auth.php получен', response);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('header.js: Данные от /api/check_auth.php', data);
            if (data.isAuthenticated) {
                window.isAuthenticated = true;
                window.userId = data.user_id;
                // Если авторизован, запрашиваем данные пользователя
                fetch('/api/get_user.php')
                    .then(response => {
                        console.log('header.js: Ответ от /api/get_user.php получен', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(userData => {
                        console.log('header.js: Данные от /api/get_user.php', userData);
                        if (userData.success) {
                            // Отображаем имя пользователя
                            accountLink.textContent = userData.user.name.toUpperCase();
                            accountLink.parentElement.href = 'account.html'; // Ссылка на страницу аккаунта
                        } else {
                            console.error('header.js: Ошибка получения данных пользователя:', userData.error);
                            accountLink.textContent = 'ВОЙТИ';
                            accountLink.parentElement.href = 'login.html';
                            window.isAuthenticated = false;
                            window.userId = null;
                        }
                    })
                    .catch(error => {
                        console.error('header.js: Ошибка при запросе данных пользователя:', error);
                        accountLink.textContent = 'ВОЙТИ';
                        accountLink.parentElement.href = 'login.html';
                        window.isAuthenticated = false;
                        window.userId = null;
                    })
                    .finally(() => {
                        // Отправляем событие после завершения
                        console.log('header.js: Отправка события authCheckComplete');
                        const event = new Event('authCheckComplete');
                        document.dispatchEvent(event);
                        console.log('header.js завершил выполнение:', { isAuthenticated: window.isAuthenticated, userId: window.userId });
                    });
            } else {
                // Если не авторизован, отображаем "ВОЙТИ"
                accountLink.textContent = 'ВОЙТИ';
                accountLink.parentElement.href = 'login.html';
                window.isAuthenticated = false;
                window.userId = null;
                // Отправляем событие после завершения
                console.log('header.js: Отправка события authCheckComplete (не авторизован)');
                const event = new Event('authCheckComplete');
                document.dispatchEvent(event);
                console.log('header.js завершил выполнение:', { isAuthenticated: window.isAuthenticated, userId: window.userId });
            }
        })
        .catch(error => {
            console.error('header.js: Ошибка проверки авторизации:', error);
            accountLink.textContent = 'ВОЙТИ';
            accountLink.parentElement.href = 'login.html';
            window.isAuthenticated = false;
            window.userId = null;
            // Отправляем событие после завершения
            console.log('header.js: Отправка события authCheckComplete (ошибка)');
            const event = new Event('authCheckComplete');
            document.dispatchEvent(event);
            console.log('header.js завершил выполнение с ошибкой:', { isAuthenticated: window.isAuthenticated, userId: window.userId });
        });
});