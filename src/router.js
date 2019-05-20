import Vue from 'vue';
import VueRouter from 'vue-router';
import {user} from './app';
Vue.use( VueRouter );

/**
 * Registrar component pages
 */

import Welcome from "./frontend/views/Welcome.vue";
import Students from "./frontend/views/Registrar/Students.vue";
import Classes from "./frontend/views/Registrar/Classes.vue";
import Requirements from "./frontend/views/Registrar/Requirements.vue";
import Teachers from "./frontend/views/Registrar/Teachers.vue";
import Subjects from "./frontend/views/Registrar/Subjects.vue";
import Login from "./frontend/views/auth/Login.vue";

/**
 * Accountant component pages
 */
import Accounts from "./frontend/views/Accountant/Accounts.vue";
import Payments from "./frontend/views/Accountant/Payments.vue";
import StatementAccount from "./frontend/views/Accountant/StatementAccount.vue";

/**
 * Errors
 */

import NotFound from "./frontend/views/errors/NotFound.vue"; 

/**
 * Middlewares access
 * 0 - administrator
 * 1 - registrar
 * 2 - accountant
 */

const routes = [
    {
        path: '*',
        component: NotFound,
        name: "404 Not Found"
    },
    {
        path: '/login',
        component: Login,
        name: "Login"
    },
    {
        path: '/',
        component: Welcome,
        name: "Welcome",
        meta: {
            requiresAuth: true,
            middleware: [0, 1, 2]
        }
    },
    {
        path: '/registrar/students',
        component: Students,
        name: "Students",
        meta: {
            requiresAuth: true,
            middleware: [0, 1]
        }
    },
    {
        path: '/registrar/classes',
        component: Classes,
        name: "Classes",
        meta: {
            requiresAuth: true,
            middleware: [0, 1]
        }
    },
    {
        path: '/registrar/requirements',
        component: Requirements,
        name: "Requirements",
        meta: {
            requiresAuth: true,
            middleware: [0, 1]
        }
    },
    {
        path: '/registrar/teachers',
        component: Teachers,
        name: "Teachers",
        meta: {
            requiresAuth: true,
            middleware: [0, 1]
        }
    },
    {
        path: '/registrar/subjects',
        component: Subjects,
        name: "Subjects",
        meta: {
            requiresAuth: true,
            middleware: [0, 1]
        }
    },
    {
        path: '/accountant/accounts',
        component: Accounts,
        name: "Accounts",
        meta: {
            requiresAuth: true,
            middleware: [0, 2]
        }
    },
    {
        path: '/accountant/payments',
        component: Payments,
        name: "Payments",
        meta: {
            requiresAuth: true,
            middleware: [0, 2]
        }
    },
    {
        path: '/accountant/statement/:id',
        component: StatementAccount,
        name: "Statement of Account", 
        meta: {
            middleware: [0, 2]
        }
    }
];

const router = new VueRouter({
    routes,
    mode: "history"
});


router.beforeEach((to, from, next) => {
    document.title = to.name + " - Enrollment and Billing System";
    if( to.matched.some( record => record.meta.requiresAuth ) ) {
        if( !user ) {
            next("/login");
        } else {
            next();
        }
    }
    else {
        if( to.path == "/login" && user ) {
            next("/");
        } else {
            next();
        }
    }
    next();
});

export default router;