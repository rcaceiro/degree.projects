/////////////THIRD PARTY COMPONENTS&PLUGINS/////////
import bootstrap from "./bootstrap";
import vue from "vue";
import VueRouter from 'vue-router';
import jwt from 'jsonwebtoken';
import VueSocketio from 'vue-socket.io';
import io from 'socket.io-client';
import VueTabs from 'vue-nav-tabs';
import VModal from 'vue-js-modal';
import VueResource from 'vue-resource';
import VueNotifications from 'vue-notifications';
import iziToast from 'izitoast';
import 'izitoast/dist/css/iziToast.min.css';
import VueGoodTable from 'vue-good-table';
require('chart.js');
require('hchs-vue-charts');

/////////////LOCAL COMPONENTS/////////
//NO AUTH ROUTES
import title from './components/title.vue';
import loginForm from './components/login.vue';
import registerForm from './components/register.vue';
import confirmEmail from './components/confirmEmail.vue';
import forgotPasswordForm from './components/forgotPassword.vue';
import resetPasswordForm from './components/resetPassword.vue';
import publicStatistics from './components/publicStatistics';
//ADMIN ROUTES
import adminArea from './components/admin/admin.vue';
import adminUsers from './components/admin/adminUsers.vue';
import adminGamePieces from './components/admin/adminGamePieces.vue';
import adminStatistics from './components/admin/adminStatistics.vue';
import adminProfile from './components/admin/adminProfile.vue';
import adminPConfig from './components/admin/adminPConfig.vue';
//PLAYER ROUTES
import playerArea from './components/player/player.vue';
import playerLobby from './components/player/playerLobby.vue';
import playerAGames from './components/player/playerGames.vue';
import playerStatistics from './components/player/playerStatistics.vue';
import playerProfile from './components/player/playerProfile.vue';
//ERRORS
import errorPage from './components/errors/error.vue';



window.Vue = vue;
window.Vue.use(VueRouter);
window.Vue.use(VueTabs);
window.Vue.use(VueResource);
window.Vue.use(VModal);
function toast ({title, message, type, timeout, buttons}) {
    if (type === VueNotifications.types.warn) type = 'warning';
    if(buttons) return iziToast[type]({title, message, timeout, buttons});
    return iziToast[type]({title, message, timeout});
}
const options = {
    success: toast,
    error: toast,
    info: toast,
    warn: toast
};
window.Vue.use(VueNotifications, options);
window.Vue.use(VueCharts);
window.Vue.use(VueGoodTable);





const routes = [
    {
        path: '/', component: title, redirect: 'login',
        children: [
            {path: 'login', component: loginForm, name: "login"},
            {path: 'forgot-password', component: forgotPasswordForm, name: "forgotPassword"},
            {path: 'register', component: registerForm, name: "register"},
            {path: 'confirm-email/:email/:token', component: confirmEmail, name: "confirmEmail"},
            {path: 'password-reset/:email/:token', component: resetPasswordForm, name: "resetPassword"},
            {path: 'error/:errorCode', component: errorPage, name: "errorPage", props: true},
            {path: 'statistics', component: publicStatistics, name: "publicStatistics"}
        ],
        meta: {
            requiresAdmin: false,
            requiresAuth: false,
        }
    },
    {
        path: '/admin', component: adminArea, redirect: '/admin/players',
        children: [
            {path: 'players', component: adminUsers, name: "adminUsers"},
            {path: 'gamepieces', component: adminGamePieces, name: "adminGamePieces"},
            {path: 'statistics', component: adminStatistics, name: "adminStatistics"},
            {path: 'profile', component: adminProfile, name: "adminProfile"},
            {path: 'config', component: adminPConfig, name: "adminPConfig"},
        ],
        meta: {
            requiresAdmin: true,
            requiresPlayer: false,
        }
    },
    {
        path: '/game', component: playerArea, redirect: '/game/lobby',
        children: [
            {path: 'lobby', component: playerLobby, name: "playerLobby"},
            {path: 'games/:gameid', component: playerAGames, name: "playerAGames"},
            {path: 'statistics', component: playerStatistics, name: "playerStatistics"},
            {path: 'profile', component: playerProfile, name: "playerProfile"},
        ],
        meta: {
            requiresAdmin: false,
            requiresPlayer: true,
        }
    },
    {path: "*", redirect: '/error/404'},

];


const router = new VueRouter({
    mode: 'history',
    routes // short for `routes: routes`
});

//ONLY ON INIT
router.beforeEach((to, from, next) => {
    if (from.name === null) {
        window.Vue.use(VueSocketio, io('http://project.dad:8080'));
        let access_token = sessionStorage.getItem('access_token');
        let refreshToken = localStorage.getItem('refresh_token');

        console.dir('Initializing...');
        if (access_token) {
            validate(access_token, refreshToken, router.app, to, next);
        } else if (refreshToken) {
            refreshTokens(refreshToken, router.app, to, next);
        } else if (to.matched.some(record => record.meta.requiresAuth)) {
            next({path: '/login'});
        } else if (to.matched.some(record => record.meta.requiresPlayer)) {
            next({path: '/login'});
        } else {
            next();
        }
    } else {
        next();
    }
});

var validate = (access_token, refreshToken, app, to, next) => {
    var temp = axios.create({
        headers: {
            Accept: 'application/json',
            Authorization: 'Bearer ' + access_token
        }
    });
    //ACCESS TOKEN VALIDATION WITH PROTECTED ROUTE
    temp.get('../../api/details')
    .then(response => {
        app.user = response.data.data;
        app.axiosInstance = temp;
        let decoded = jwt.decode(access_token, {complete: true});

            if (to.name === 'login') {
                if (decoded.payload.scopes.includes('admin')) {
                    next({path: '/admin'});
                } else {
                    next({path: '/game'});
                }
            }

            if (!decoded.payload.scopes.includes('admin') &&
                to.matched.some(record => record.meta.requiresAdmin)) {
                next({path: '/game'});
            } else if(decoded.payload.scopes.includes('admin') &&
                to.matched.some(record => record.meta.requiresPlayer)) {
                next({path: '/admin'});
            } else{
                next();
            }
            //IF INVALID TRY TO REFRESH
        }).catch(() => {
        app.axiosInstance = null;
        if (refreshToken) {
            refreshTokens(refreshToken, app, to, next);
        } else {
            sessionStorage.removeItem('access_token');
            next({path: '/login'});
        }
    });
};

var refreshTokens = (refreshToken, app, to, next) => {
    axios.post('../api/login/refresh', {refreshToken: refreshToken})
        .then(response => {
            let access_token = response.data.access_token;
            let new_refresh_token = response.data.refresh_token;

            sessionStorage.setItem('access_token', access_token);
            localStorage.setItem('refresh_token', new_refresh_token);

            //ADD TOKEN TO AXIOS INSTANCE
            validate(access_token, refreshToken, app, to, next);
        })
        .catch(() => {
            sessionStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            next({path: '/login'});
        });
};

const app = new window.Vue({
    data: {
        title: 'MemoryGame',
        user: '',
        axiosInstance: null
    },
    router,
    methods: {
        initAxios: function (access_token) {
            this.axiosInstance = axios.create({
                headers: {
                    Accept: 'application/json',
                    Authorization: 'Bearer ' + access_token
                }
            });
        },
        getUser: function (callback) {
            this.axiosInstance.get('../../api/details')
                .then(response => {
                    this.user = response.data.data;
                    callback();
                });
        },
        logout: function () {
            if (this.axiosInstance) {
                this.axiosInstance.post('api/logout');
            }
            sessionStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            this.axiosInstance = null;
            this.user = {};
            this.$router.push({path: '/'});
        },
        url: function (url) {
            let uriPathSize = (this.$route.path.split("/").length - 2);
            for (let i = 0; i < uriPathSize; ++i) {
                url = '../' + url;
            }
            return url;
        },
        pushLaravelValidationErrorsTo: function (errorMsgArray, response, customMessage = 'Error:') {
            errorMsgArray.splice(0);
            errorMsgArray.push(customMessage);

            //ADD LARAVEL ERROR MSGS TO ARRAY
            if (response.response.data.errors !== null) {
                let errors = Object.values(response.response.data.errors);
                errors.forEach((elem) => {
                    errorMsgArray.push(" -" + elem[0]);
                });
            } else {
                errorMsgArray.push("An unexpected error occurred");
            }
        }
    }
}).$mount('#app');