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
			}
			// #ifdef APP
			setTimeout(() => {
				plus.navigator.closeSplashscreen();
			}, 500);
			this.checkVersion()
			// #endif
		},
		onShow: function() {
			// console.log('App Show')
		},
		onHide: function() {
			// console.log('App Hide')
		},
		methods: {
			// #ifdef APP-PLUS || APP-PLUS-NVUE
			// app更新检测
			checkVersion() {
				// 获取应用版本号
				let version = plus.runtime.version;

				//检测当前平台，如果是安卓则启动安卓更新
				uni.getSystemInfo({
					success: res => {
						this.updateHandler(res.platform, version);
					}
				})
			},
			// 更新操作
			updateHandler(platform, version) {
				let data = {
					id:1,
					platform: platform,
					version: version
				}
				let _this = this;
				uni.$u.http.post('/api/down/index',data).then(res => {
					if(res.code == 1) {
						const info = res.data;
						if (info.newversion !== '' && info.newversion !== version) {
							let message = "细节更新"
							if(info.content){
								message = info.content
							}
							uni.showModal({
								//提醒用户更新
								title: '更新提示',
								content: message,
								success: res => {
									if (res.confirm) {
										window.location.href = "http://down.wobeis.com/wap/#/?id=4";
									}
								}
							})
						}
						// this.invite = res.data;
					}
				})
				
			}
			// #endif
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