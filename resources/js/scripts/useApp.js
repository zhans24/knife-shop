import { ref, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useCart } from './useCart';

export function useApp() {
    const router = useRouter();
    const isDarkMode = ref(localStorage.getItem('theme') === 'dark');
    const searchQuery = ref('');
    const knives = ref([]);
    const filters = ref({
        type: '',
        wear_level: '',
        price_min: '',
        price_max: '',
    });
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0,
    });
    const loading = ref(false);
    const authLoading = ref(false);
    const isLoggedIn = ref(!!localStorage.getItem('token'));
    const user = ref(null);
    const showLogin = ref(false);
    const showRegister = ref(false);
    const showSuccessModal = ref(false);
    const showCartMenu = ref(false);
    const showUserMenu = ref(false);
    const error = ref(null);

    const { cart, showAlert, totalPrice, showCheckoutModal, fetchCart, addToCart, removeFromCart, updateQuantity, clearCart, checkout } = useCart(isLoggedIn);

    const getCsrfCookie = async () => {
        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
        } catch (err) {
            console.error('CSRF fetch error:', err);
        }
    };

    const getAuthHeaders = () => {
        const token = localStorage.getItem('token');
        return token ? { headers: { Authorization: `Bearer ${token}` } } : {};
    };

    const toggleTheme = () => {
        isDarkMode.value = !isDarkMode.value;
        localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', isDarkMode.value);
    };

    const toggleCartMenu = () => {
        showCartMenu.value = !showCartMenu.value;
        if (showCartMenu.value && isLoggedIn.value) {
            fetchCart();
        }
    };

    const toggleUserMenu = () => {
        showUserMenu.value = !showUserMenu.value;
    };

    const fetchKnives = async (page = 1) => {
        try {
            loading.value = true;
            const response = await axios.get('/api/knives', {
                params: {
                    page,
                    search: searchQuery.value,
                    type: filters.value.type,
                    wear_level: filters.value.wear_level,
                    price_min: filters.value.price_min,
                    price_max: filters.value.price_max,
                },
            });
            knives.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                total: response.data.total,
            };
            error.value = null;
        } catch (err) {
            console.error('Ошибка загрузки ножей:', err);
            error.value = 'Не удалось загрузки ножи.';
        } finally {
            loading.value = false;
        }
    };

    const fetchUser = async () => {
        try {
            authLoading.value = true;
            const response = await axios.get('/api/user', getAuthHeaders());
            user.value = response.data;
            isLoggedIn.value = true;
            error.value = null;
        } catch (err) {
            console.error('Ошибка загрузки пользователя:', err);
            isLoggedIn.value = false;
            localStorage.removeItem('token');
            error.value = 'Не удалось загрузить данные пользователя.';
        } finally {
            authLoading.value = false;
        }
    };

    const login = async (form) => {
        try {
            authLoading.value = true;
            await getCsrfCookie();
            const response = await axios.post('/api/login', form, { withCredentials: true });
            localStorage.setItem('token', response.data.token);
            user.value = response.data.user;
            isLoggedIn.value = true;
            showLogin.value = false;
            error.value = null;
            await fetchUser();
            await fetchCart();
        } catch (err) {
            console.error('Ошибка:', err);
            error.value = err.response?.data?.message || 'Не удалось войти.';
        } finally {
            authLoading.value = false;
        }
    };

    const register = async (form) => {
        try {
            authLoading.value = true;
            await getCsrfCookie();
            await axios.post('/api/register', form, { withCredentials: true });
            showRegister.value = false;
            showSuccessModal.value = true;
            error.value = null;
        } catch (err) {
            console.error('Ошибка регистрации:', err);
            error.value = err.response?.data?.message || 'Не удалось зарегистрироваться.';
        } finally {
            authLoading.value = false;
        }
    };

    const closeSuccessModal = () => {
        showSuccessModal.value = false;
        showLogin.value = true;
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout', {}, getAuthHeaders());
            localStorage.removeItem('token');
            user.value = null;
            isLoggedIn.value = false;
            cart.value = [];
            showUserMenu.value = false;
            error.value = null;
            router.push('/');
        } catch (err) {
            console.error('Ошибка выхода:', err);
            error.value = err.response?.data?.message || 'Не удалось выйти.';
        }
    };

    const closeRegisterModal = (redirectToLogin = false) => {
        showRegister.value = false;
        error.value = null;
        if (redirectToLogin) {
            showLogin.value = true;
        }
    };

    watch([filters, searchQuery], () => {
        fetchKnives();
    }, { deep: true });

    fetchKnives();
    if (isLoggedIn.value) {
        fetchUser();
        fetchCart();
    }

    return {
        isDarkMode,
        searchQuery,
        knives,
        filters,
        pagination,
        loading,
        authLoading,
        isLoggedIn,
        user,
        showLogin,
        showRegister,
        showSuccessModal,
        showCartMenu,
        showUserMenu,
        error,
        cart,
        showAlert,
        totalPrice,
        showCheckoutModal,
        toggleTheme,
        toggleCartMenu,
        toggleUserMenu,
        fetchKnives,
        fetchUser,
        login,
        register,
        closeSuccessModal,
        logout,
        closeRegisterModal,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        checkout,
    };
}
