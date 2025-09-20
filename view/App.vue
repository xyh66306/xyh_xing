<script>
	export default {
		onLaunch: async function(e) {
			// console.log('App Launch')
			// console.log(e);
			let token = uni.getStorageSync('token');
			
			if (!token) {
				var nologin = ['pages/register/register', 'pages/login/login', 'pages/payment/payment'];
				if (nologin.indexOf(e.path) === -1) {
					uni.redirectTo({
						url: '/pages/login/login'
					})
				}
				this.getMenu(1);
			} else {
				await uni.$u.http.post('/api/user/getUserinfo').then((res) => {
					if(res.code == 1) {
						uni.setStorageSync('user', res.data);
						if(res.data.group_id==2){
							this.getMenu(2);
						} else {
							this.getMenu(1);
						}
					}
				})
			}
			// #ifdef APP
			setTimeout(() => {
				plus.navigator.closeSplashscreen();
			}, 500);
			// #endif
		},
		onShow: function() {
			// console.log('App Show')
		},
		onHide: function() {
			// console.log('App Hide')
		},
		methods: {
			getMenu(group){
				uni.setStorageSync('user_group', group);
			},
			// uploadFile(url) {
			// 	// #ifdef APP
			// 	const host = 'http://103.97.176.57';
			// 	// #endif
			// 	// #ifndef APP
			// 	const host = location.origin;
			// 	// #endif
			// 	return new Promise((resolve, reject) => {
			// 		let a = uni.uploadFile({
			// 			url: host + '/api/common/upload',
			// 			filePath: url,
			// 			name: 'file',
			// 			formData: {},
			// 			success: (res) => {
			// 				console.log(res);
			// 				if(res.statusCode == 200) {
			// 					let data = JSON.parse(res.data);
			// 					resolve(data)
			// 				}else{
			// 					reject({});
			// 				}
			// 			}
			// 		});
			// 	})
			// }
		}
	}
</script>

<style lang="scss">
	/* 注意要写在第一行，同时给style标签加入lang="scss"属性 */
	@import "@/uni_modules/uview-ui/index.scss";

	page {
		background-color: #F1F4F9;
	}

	view {
		box-sizing: border-box;
	}
</style>