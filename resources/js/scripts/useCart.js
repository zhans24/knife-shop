import { ref, computed } from 'vue';
import axios from 'axios';

export function useCart(isLoggedIn) {
    const cart = ref([]);
    const showAlert = ref(false);
    const error = ref(null);
    const showCheckoutModal = ref(false);

    const totalPrice = computed(() => {
        return cart.value.reduce((sum, item) => {
            const itemPrice = typeof item.price === 'number' ? item.price : 0;
            return sum + itemPrice * item.quantity;
        }, 0).toFixed(2);
    });

    const getAuthHeaders = () => {
        const token = localStorage.getItem('token');
        return token ? { headers: { Authorization: `Bearer ${token}` } } : {};
    };

    const fetchCart = async () => {
        try {
            const response = await axios.get('/api/cart', getAuthHeaders());
            cart.value = Array.isArray(response.data) ? response.data : [];
            error.value = null;
        } catch (err) {
            console.error('Ошибка загрузки корзины:', err.response?.data);
            error.value = err.response?.data?.message || 'Не удалось загрузить корзину.';
            if (err.response?.status === 401) {
                showAlert.value = true;
                localStorage.removeItem('token');
            }
        }
    };

    const addToCart = async (knife) => {
        if (!isLoggedIn.value) {
            showAlert.value = true;
            return;
        }
        try {
            const response = await axios.post(
                '/api/cart/add',
                { knife_id: knife.id },
                getAuthHeaders()
            );
            cart.value = Array.isArray(response.data) ? response.data : [];
            error.value = null;
        } catch (err) {
            console.error('Full error:', err);
            error.value = err.response?.data?.message || 'Не удалось добавить в корзину.';
            if (err.response?.status === 401) {
                showAlert.value = true;
                localStorage.removeItem('token');
            }
        }
    };

    const removeFromCart = async (knifeId) => {
        try {
            const response = await axios.post(
                '/api/cart/remove',
                { knife_id: knifeId },
                getAuthHeaders()
            );
            cart.value = Array.isArray(response.data) ? response.data : [];
            error.value = null;
        } catch (err) {
            console.error('Ошибка удаления из корзины:', err.response?.data);
            error.value = err.response?.data?.message || 'Не удалось удалить из корзины.';
            if (err.response?.status === 401) {
                showAlert.value = true;
                localStorage.removeItem('token');
            }
        }
    };

    const updateQuantity = async (knifeId, quantity) => {
        if (quantity < 1) return;
        try {
            const response = await axios.post(
                '/api/cart/update-quantity',
                { knife_id: knifeId, quantity },
                getAuthHeaders()
            );
            cart.value = Array.isArray(response.data) ? response.data : [];
            error.value = null;
        } catch (err) {
            error.value = err.response?.data?.message || 'Не удалось обновить количество.';
            if (err.response?.status === 401) {
                showAlert.value = true;
                localStorage.removeItem('token');
            }
        }
    };

    const clearCart = async () => {
        try {
            await axios.post('/api/cart/clear', {}, getAuthHeaders());
            cart.value = [];
            error.value = null;
        } catch (err) {
            console.error('Ошибка очистки корзины:', err.response?.data);
            error.value = err.response?.data?.message || 'Не удалось очистить корзину.';
            if (err.response?.status === 401) {
                showAlert.value = true;
                localStorage.removeItem('token');
            }
        }
    };

    const checkout = async () => {
        try {
            showCheckoutModal.value = true; // Показываем модальное окно
        } catch (err) {
            console.error('Ошибка оформления заказа:', err);
            error.value = 'Не удалось оформить заказ.';
        }
    };

    return {
        cart,
        showAlert,
        error,
        totalPrice,
        showCheckoutModal,
        fetchCart,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        checkout,
    };
}
