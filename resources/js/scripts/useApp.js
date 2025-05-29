import { ref, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useCart } from './useCart';

export function useApp() {
    const router = useRouter();
    const isLoggedIn = ref(false);

    const fetchCsrfToken = async () => {
        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
        } catch (error) {
            console.error('Failed to fetch CSRF token:', error.response?.data || error.message);
        }
    };

    const {
        cart,
        showAlert,
        totalPrice,
        fetchCart,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        checkout,
    } = useCart(isLoggedIn, fetchCsrfToken);

    // State
    const knives = ref([]);
    const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
    const filters = ref({ type: '', wear_level: '', price_min: '', price_max: '', search: '' });
    const searchQuery = ref('');
    const loading = ref(false);
    const user = ref(null);
    const authLoading = ref(true);
    const showLogin = ref(false);
    const showRegister = ref(false);
    const showUserMenu = ref(false);
    const showCartMenu = ref(false);
    const isDarkMode = ref(false);
    const error = ref(null);

    const fetchKnives = async (page = 1) => {
        try {
            loading.value = true;
            error.value = null;
            knives.value = [];
            const params = { ...filters.value, search: searchQuery.value.trim(), page };
            const response = await axios.get('/api/knives', { params });
            if (Array.isArray(response.data.data)) {
                knives.value = response.data.data.map((knife) => ({
                    ...knife,
                    price: knife.price ? parseFloat(knife.price) : null,
                }));
                pagination.value = {
                    current_page: response.data.current_page || 1,
                    last_page: response.data.last_page || 1,
                    total: response.data.total || 0,
                };
            } else {
                throw new Error('Invalid API response');
            }
        } catch (err) {
            console.error('Ошибка загрузки ножей:', err);
            error.value = err.response?.data?.message || 'Не удалось загрузить данные.';
        } finally {
            loading.value = false;
        }
    };

    const checkAuth = async () => {
        authLoading.value = true;
        const token = localStorage.getItem('token');
        if (token) {
            try {
                await fetchCsrfToken();
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`; // Исправлено: убрана лишняя кавычка
                const response = await axios.get('/api/user', { withCredentials: true });
                if (response.data && typeof response.data === 'object') {
                    isLoggedIn.value = true;
                    user.value = response.data;
                    await fetchCart();
                } else {
                    throw new Error('Invalid user data');
                }
            } catch (err) {
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
                isLoggedIn.value = false;
                user.value = null;
            }
        } else {
            isLoggedIn.value = false;
            user.value = null;
        }
        authLoading.value = false;
    };

    const login = async (credentials) => {
        try {
            await fetchCsrfToken();
            const response = await axios.post('/api/login', credentials, { withCredentials: true });
            if (response.data.token && response.data.user) {
                localStorage.setItem('token', response.data.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
                isLoggedIn.value = true;
                user.value = response.data.user;
                showLogin.value = false;
                error.value = null;
                await fetchCart();
                router.push('/');
            }
        } catch (error) {
            console.error('Ошибка :', error);
            if (error.response?.status === 422) {
                error.value = error.response.data.errors?.email?.[0] || 'Неверные данные.';
            } else {
                error.value = error.response?.data?.message || 'Ошибка входа.';
            }
        }
    };

    const register = async (data) => {
        try {
            await fetchCsrfToken();
            const response = await axios.post('/api/register', data, { withCredentials: true });
            if (response.data.token && response.data.user) {
                localStorage.setItem('token', response.data.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
                isLoggedIn.value = true;
                user.value = response.data.user;
                showRegister.value = false;
                error.value = null;
                await fetchCart();
                router.push('/');
            }
        } catch (error) {
            console.error('Ошибка регистрации:', error);
            error.value = error.response?.data?.message || 'Ошибка регистрации.';
        }
    };

    const logout = async () => {
        try {
            await fetchCsrfToken();
            await axios.post(
                '/api/logout',
                {},
                {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                    withCredentials: true,
                }
            );
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
            isLoggedIn.value = false;
            user.value = null;
            cart.value = [];
            showUserMenu.value = false;
            error.value = null;
            router.push('/');
        } catch (error) {
            console.error('Ошибка выхода:', error);
            error.value = error.response?.data?.message || 'Не удалось выйти.';
        }
    };

    const toggleTheme = () => {
        isDarkMode.value = !isDarkMode.value;
        localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
        document.body.classList.toggle('dark', isDarkMode.value);
    };

    const loadTheme = () => {
        const theme = localStorage.getItem('theme');
        isDarkMode.value = theme === 'dark';
        document.body.classList.toggle('dark', isDarkMode.value);
    };

    const toggleUserMenu = () => {
        showUserMenu.value = !showUserMenu.value;
    };

    const toggleCartMenu = () => {
        showCartMenu.value = !showCartMenu.value;
    };

    loadTheme();
    checkAuth();
    fetchKnives();

    watch(filters, () => fetchKnives(), { deep: true });
    watch(searchQuery, () => {
        filters.value.search = searchQuery.value;
        fetchKnives();
    });

    return {
        knives,
        pagination,
        filters,
        searchQuery,
        loading,
        isLoggedIn,
        user,
        authLoading,
        showLogin,
        showRegister,
        showAlert,
        showUserMenu,
        showCartMenu,
        isDarkMode,
        error,
        cart,
        totalPrice,
        fetchKnives,
        checkAuth,
        login,
        register,
        logout,
        toggleTheme,
        loadTheme,
        toggleUserMenu,
        toggleCartMenu,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        checkout,
    };
}
