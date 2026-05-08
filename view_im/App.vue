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
			this.initPush() // 初始化推送服务
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

            // 初始化推送服务
            initPush() {
                // 监听推送消息
                plus.push.addEventListener("click", (msg) => {
                    console.log("点击推送消息:", msg);
                    this.handlePushMessage(msg);
                }, false);

                // 监听在线推送消息
                plus.push.addEventListener("receive", (msg) => {
                    console.log("收到推送消息:", msg);
                    this.handlePushMessage(msg);
                }, false);

                // 获取客户端标识
                plus.push.getClientInfo((info) => {
                    console.log("客户端信息:", info);
                    // 可以将 clientid 发送到服务器用于定向推送
                    if (info && info.clientid) {
                        this.saveClientInfo(info.clientid);
                    }
                });
            },
            // 处理推送消息
            handlePushMessage(msg) {
                try {
                    let payload = {};
                    if (typeof msg.payload === 'string') {
                        payload = JSON.parse(msg.payload);
                    } else {
                        payload = msg.payload || {};
                    }

                    // 根据推送类型处理不同业务
                    if (payload.type === 'version_update') {
                        // 版本更新推送
                        this.showUpdateDialog(payload.data);
                    } else if (payload.type === 'notification') {
                        // 普通通知
                        uni.showToast({
                            title: payload.title || '新消息',
                            icon: 'none'
                        });
                    }
                } catch (e) {
                    console.error("处理推送消息失败:", e);
                }
            },
            // 保存客户端信息到服务器
            saveClientInfo(clientid) {
                const token = uni.getStorageSync('token');
				cosnsole.log("clientid",clientid)
                if (token) {
                    uni.$u.http.post('/api/push/saveClientInfo', {
                        clientid: clientid,
                        platform: plus.os.name.toLowerCase()
                    }).then(res => {
                        console.log("客户端信息保存成功:", res);
                    }).catch(err => {
                        console.error("保存客户端信息失败:", err);
                    });
                }
            },

            // 显示更新对话框
            showUpdateDialog(updateData) {
                if (!updateData) return;

                uni.showModal({
                    title: updateData.title || '版本更新',
                    content: updateData.content || '发现新版本，是否立即更新？',
                    showCancel: updateData.force !== true, // 强制更新时不显示取消按钮
                    cancelText: '稍后',
                    confirmText: '立即更新',
                    success: (res) => {
                        if (res.confirm) {
                            this.downloadAndInstall(updateData);
                        }
                    }
                });
            },	
           // 下载并安装更新
            downloadAndInstall(updateData) {
                if (!updateData || !updateData.url) {
                    uni.showToast({
                        title: '下载地址无效',
                        icon: 'none'
                    });
                    return;
                }

                uni.showLoading({
                    title: '下载中...'
                });

                // 下载更新包
                uni.downloadFile({
                    url: updateData.url,
                    success: (downloadResult) => {
                        uni.hideLoading();
                        
                        if (downloadResult.statusCode === 200) {
                            // 安装更新
                            plus.runtime.install(
                                downloadResult.tempFilePath,
                                {},
                                () => {
                                    uni.showToast({
                                        title: '安装成功，即将重启',
                                        icon: 'success'
                                    });
                                    
                                    // 延迟重启应用
                                    setTimeout(() => {
                                        plus.runtime.restart();
                                    }, 1500);
                                },
                                (error) => {
                                    uni.showToast({
                                        title: '安装失败',
                                        icon: 'none'
                                    });
                                    console.error("安装失败:", error);
                                }
                            );
                        } else {
                            uni.showToast({
                                title: '下载失败',
                                icon: 'none'
                            });
                        }
                    },
                    fail: (error) => {
                        uni.hideLoading();
                        uni.showToast({
                            title: '网络错误',
                            icon: 'none'
                        });
                        console.error("下载失败:", error);
                    }
                });
            },											

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
										plus.runtime.openURL("http://down.wobeis.com/wap/#/?id=4");
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