import App from './App'


import store from './store'
// import payTabbar from '@/components/pay-tabbar/pay-tabbar.vue'

// Vue.component('payTabbar', payTabbar)
Vue.prototype.$store = store;

// #ifndef VUE3
import Vue from 'vue'
import './uni.promisify.adaptor'

Vue.config.productionTip = false
import uView from '@/uni_modules/uview-ui'
Vue.use(uView)
App.mpType = 'app'
const app = new Vue({
	...App
})

require('@/common/request.js')(app)

app.$mount()
// #endif

// #ifdef VUE3
import {
	createSSRApp
} from 'vue'
import uView from '@/uni_modules/uview-ui'
Vue.use(uView)
export function createApp() {
	const app = createSSRApp(App)
	return {
		app
	}
}
// #endif