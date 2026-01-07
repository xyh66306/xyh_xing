<template>
	<view class="container">
		<!-- 订单信息 -->
		<view class="order-info">
			<view class="section-title">订单信息</view>
			<view class="order-item">
				<text>金额：</text>
				<text class="highlight">{{amount}}</text>
			</view>				
			<view class="order-item">
				<text>有效时间：</text>
				<text class="highlight"><uni-countdown :showDay='false' :showHour='false' :minute="yx_time_min" :second="yx_time_sec"></uni-countdown></text>
			</view>			
		</view>

		<view class="payment-methods">
			<view class="section-title">选择支付方式</view>
			<view v-for="method in paymentMethods" :key="method.value" class="method-btn"
				:class="{ active: selectedMethod === method.value }" @click="selectMethod(method.value)"
				v-if="method.isShow">
				<view :class="['method-icon', method.icon]"></view>
				<text>{{ method.label }}</text>
			</view>
		</view>

		<!-- 支付详情区域 -->
		<view class="payment-details" v-if="selectedMethod">
			<!-- 微信支付二维码 -->
			<view v-if="selectedMethod === 'wxpay'" class="payment-info">
				<view class="section-title">微信支付</view>
				<view class="form-item">
					<text class="form-label">收款人姓名</text>
					<input v-model="username" placeholder="请输入姓名" disabled class="form-input" />
				</view>					
				<view class="qrcode-container">
					<image :src="wechatQRCode" class="qrcode-image" mode="aspectFit"></image>
					<text class="qrcode-tip">请打开微信扫描二维码支付</text>
				</view>
			</view>

			<!-- 支付宝支付二维码 -->
			<view v-if="selectedMethod === 'alipay'" class="payment-info">
				<view class="section-title">支付宝支付</view>
				<view class="form-item">
					<text class="form-label">收款人姓名</text>
					<input v-model="username" placeholder="请输入姓名" disabled class="form-input" />
				</view>				
				<view class="qrcode-container">
					<image :src="alipayQRCode" class="qrcode-image" mode="aspectFit"></image>
					<text class="qrcode-tip">请打开支付宝扫描二维码支付</text>
				</view>
			</view>

			<!-- 银行卡支付表单 -->
			<view v-if="selectedMethod === 'bank'" class="payment-info">
				<view class="section-title">银行卡支付</view>
				<view class="bank-form">
					<view class="form-item">
						<text class="form-label">收款人姓名</text>
						<input v-model="bankCard.holder" disabled placeholder="请输入姓名" class="form-input" />
					</view>
					<view class="form-item">
						<text class="form-label">银行名称</text>
						<input v-model="bankCard.bankname" disabled placeholder="请输入卡号" class="form-input" maxlength="19" />
					</view>
					<view class="form-item">
						<text class="form-label">银行卡号</text>
						<input v-model="bankCard.number" disabled type="number" placeholder="请输入卡号" class="form-input"
							maxlength="19" />
					</view>
					<view class="form-item">
						<text class="form-label">支行地址</text>
						<input v-model="bankCard.address" disabled type="text" placeholder="请输入卡号" class="form-input"
							maxlength="19" />
					</view>


				</view>
			</view>
		</view>
		<view class="tips">
			<view class="hd">温馨提示</view>
			<view class="bd">
				<view>请认真阅读以下交易说明:</view>
				<view>1、禁止将任何非法资金（如赌博，跑分，资金盘，诈骗）用于交易的流程，一经发现将永久冻结账户，并上报至平台从严处理。</view>
				<view>2、转账完成后,请务必 <text style="color:#f00">上传支付凭证图片</text>点击”提交凭证”，否则会影响放币进度;若您未完成转账，请不要点击此按钮，否则可能会影响您账户的部分功能。</view>
			</view>
		</view>
		<!-- 支付按钮 -->
		<button class="pay-btn" :disabled="!selectedMethod || !isPaymentReady" @click="handlePayment">
			{{ payButtonText }}
		</button>

		<!-- 凭证上传区域 -->
		<view class="upload-section" v-show="showUploadSection">
			<view class="section-title">请上传支付凭证：</view>
			<view class="upload-area" @click="openImagePicker">
				<view class="upload-icon">+</view>
				<text>点击上传支付凭证</text>
				<text class="upload-tip">支持JPG/PNG格式，大小不超过5MB</text>
			</view>

			<!-- 图片预览 -->
			<scroll-view class="preview-container" scroll-x>
				<view v-for="(img, index) in previewImages" :key="index" class="image-wrapper">
					<image :src="img" class="preview-image" mode="aspectFill" @click="previewImage(index)"></image>
					<view class="delete-btn" @click="removeImage(index)">×</view>
				</view>
			</scroll-view>

			<button class="submit-upload" @click="submitProof">提交凭证</button>
		</view>
	</view>
</template>

<script>
	const base_url = "https://bingocn.wobeis.com"
	export default {
		data() {
			return {
				diqu:1,
				orderid: '',
				access_key: '',
				amount: '',
				actNum: '',
				orderTime: '',
				ctime: '',
				username:"",
				selectedMethod: null,
				selectedBank: null,
				payButtonText: '立即转账',
				showUploadSection: false,
				previewImages: [],
				paymentMethods: [{
						label: '微信支付',
						value: 'wxpay',
						icon: 'wechat-icon',
						isShow: false
					},
					{
						label: '支付宝支付',
						value: 'alipay',
						icon: 'alipay-icon',
						isShow: false
					},
					{
						label: '银行卡支付',
						value: 'bank',
						icon: 'bank-icon',
						isShow: false
					}
				],
				pinzheng: '',
				bankCard: {
					holder: '',
					bankname: '',
					number: '',
					address: '',
				},
				wechatQRCode: '',
				alipayQRCode: '',
				yx_time_min:0,
				yx_time_sec:0,
				token:'',
			};
		},
		computed: {
			// 检查支付是否就绪
			isPaymentReady() {
				if (this.selectedMethod === 'bank') {
					return (
						this.bankCard.bankname.length > 0 &&
						this.bankCard.number.length >= 16 &&
						this.bankCard.holder.length > 0
					);
				}
				return true;
			}
		},
		onLoad(e) {
			let that = this
			if (e.access_key) {
				that.access_key = e.access_key				
			}
			if(e.diqu){
				that.diqu = e.diqu
			}
			if(e.orderid){
				that.orderid = e.orderid
			}
			that.getDetails()
		},
		methods: {
			getDetails() {
				let that = this
				uni.request({
					url: base_url + '/openapi/details/index',
					method: "POST",
					data: {
						orderid:that.orderid,
						access_key: that.access_key,
						diqu:that.diqu,
					},
					success: (res) => {
						if (res.data.code == 1) {
							let data = res.data.data
							// that.selectedMethod = data.pay_type
							that.yx_time_min = data.yx_time_min
							that.yx_time_sec = data.yx_time_sec
							that.amount = data.amount
							that.actNum = data.act_num
							that.ctime = data.ctime
							that.payButtonText = '立即支付 ¥' + data.amount;
							that.token = data.token
							if (data.bankInfo) {
								that.bankCard.bankname = data.bankInfo.bank_name
								that.bankCard.holder = data.bankInfo.username
								that.bankCard.number = data.bankInfo.bank_nums
								that.bankCard.address = data.bankInfo.bank_zhmc
								that.paymentMethods[2]['isShow'] = true
							}
							if (data.wxpay) {
								that.username = data.wxpay.username
								that.wechatQRCode = data.wxpay.pay_ewm_image
								that.paymentMethods[0]['isShow'] = true
							}
							if (data.alipay) {
								that.username = data.alipay.username
								that.alipayQRCode = data.alipay.pay_ewm_image
								that.paymentMethods[1]['isShow'] = true
							}
							that.paymentMethods.forEach(res=>{
								if(res.isShow){
									that.selectedMethod = res.value
								}
							})

						}
					}

				})
			},
			selectMethod(method) {
				this.selectedMethod = method;

			},

			selectBank(bankCode) {
				this.selectedBank = bankCode;
			},

			// 格式化银行卡号显示
			formatCardNumber(e) {
				let value = e.detail.value.replace(/\s/g, '').replace(/\D/g, '');

				// 分组显示，每4位一组
				let formatted = '';
				for (let i = 0; i < value.length; i++) {
					if (i > 0 && i % 4 === 0) {
						formatted += ' ';
					}
					formatted += value[i];
				}

				this.bankCard.number = formatted;
			},

			// 格式化有效期显示
			formatExpiry(e) {
				let value = e.detail.value.replace(/\D/g, '');

				if (value.length > 2) {
					this.bankCard.expiry = value.substring(0, 2) + '/' + value.substring(2, 4);
				} else {
					this.bankCard.expiry = value;
				}
			},

			async handlePayment() {
				if (!this.selectedMethod) return;
				if (this.selectedMethod === 'bank' && !this.isPaymentReady) {
					uni.showToast({
						title: '请填写完整的银行卡信息',
						icon: 'none'
					});
					return;
				}

				// 更新按钮状态
				this.payButtonText = '支付处理中...';

				try {
					// // 模拟支付过程
					// await new Promise(resolve => setTimeout(resolve, 2000));

					// // 支付成功处理
					// uni.showToast({
					//   title: `支付成功！您选择了${this.getMethodName()}支付`,
					//   icon: 'success'
					// });

					// 显示凭证上传区域
					this.showUploadSection = true;
					this.$nextTick(() => {
						uni.pageScrollTo({
							selector: '.upload-section',
							duration: 300
						});
					});

				} catch (error) {
					uni.showToast({
						title: '支付失败，请重试',
						icon: 'none'
					});
				} finally {
					// 恢复按钮状态
					// this.payButtonText = '立即支付 ¥1299.00';
				}
			},

			getMethodName() {
				const methodMap = {
					'wechat': '微信',
					'alipay': '支付宝',
					'bank': '银行卡'
				};
				return methodMap[this.selectedMethod] || '未知';
			},

			openImagePicker() {
				let that = this
				uni.chooseImage({
					count: 1,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success: (chooseImageRes) => {
						this.previewImages = this.previewImages.concat(chooseImageRes.tempFilePaths);

						const tempFilePaths = chooseImageRes.tempFilePaths;
						const uploadTask = uni.uploadFile({
							url: base_url + '/api/common/upload',
							filePath: tempFilePaths[0],
							fileType: 'image',
							name: 'file',
							headers: {
								'Accept': 'application/json',
								'Content-Type': 'multipart/form-data',
							},
							formData: {
								'method': 'images.upload',
								'upfile': tempFilePaths[0]
							},
							success: (uploadFileRes) => {
								let resinfo = JSON.parse(uploadFileRes.data)
								if(that.pinzheng){
									that.pinzheng = that.pinzheng + "," +resinfo.data.fullurl
								} else {
									that.pinzheng = resinfo.data.fullurl
								}
							},
							fail: (error) => {
								if (error && error.response) {
									showError(error.response);
								}
							},
						});


					}
				});
			},

			previewImage(index) {
				uni.previewImage({
					current: index,
					urls: this.previewImages
				});
			},

			removeImage(index) {
				this.previewImages.splice(index, 1);
			},

			submitProof() {
				let that = this
				if (this.previewImages.length === 0) {
					uni.showToast({
						title: '请至少上传一张支付凭证',
						icon: 'none'
					});
					return;
				}
								
				// 模拟凭证上传
				uni.request({
					url: base_url + "/openapi/details/payorder",
					method: "POST",
					data: {
						access_key: that.access_key,
						orderid: that.orderid,
						pay_type: that.selectedMethod,
						pinzheng_image: that.pinzheng,
						auth_token:that.token,
					},
					success(res) {
						let data = res.data
						if (data.code == 1) {
							uni.showToast({
								title: '凭证提交成功！',
								icon: 'success'
							});
							uni.navigateTo({
								url:'/pages/result/index?orderid='+that.orderid + "&access_key="+that.access_key
							})
						} else {
							uni.showToast({
								title: data.msg,
								icon: 'none'
							});
						}
					}
				})
				// 可以添加提交成功后的跳转逻辑
				// setTimeout(() => {
				//   uni.navigateBack();
				// }, 1500);
			}
		}
	};
</script>

<style>
	.container {
		padding: 20px;
		background-color: #f5f5f5;
		min-height: 100vh;
	}

	.section-title {
		font-size: 18px;
		font-weight: bold;
		margin-bottom: 15px;
		padding-left: 10px;
		border-left: 3px solid #ff6600;
	}

	.section-subtitle {
		font-size: 16px;
		color: #666;
		margin-bottom: 10px;
		display: block;
	}

	.order-info {
		background-color: #fff;
		border-radius: 10px;
		padding: 15px;
		margin-bottom: 20px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
	}

	.order-item {
		display: flex;
		justify-content: space-between;
		padding: 8px 5px;
		font-size: 14px;
	}

	.highlight {
		color: #e4393c;
		font-weight: bold;
	}

	.payment-methods {
		background-color: #fff;
		border-radius: 10px;
		padding: 15px;
		margin-bottom: 20px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
	}

	.method-btn {
		display: flex;
		align-items: center;
		padding: 15px;
		margin-bottom: 15px;
		border: 1px solid #ddd;
		border-radius: 8px;
		background: white;
		transition: all 0.3s;
	}

	.method-btn:active {
		background-color: #f8f8f8;
	}

	.method-btn.active {
		border-color: #ff6600;
		background: #fff8f0;
	}

	.method-icon {
		width: 40px;
		height: 40px;
		margin-right: 15px;
		background-size: contain;
		background-repeat: no-repeat;
	}

	.wechat-icon {
		background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%2309bb07" d="M8.2 13.6h1.6v1.6H8.2zm3.2 0h1.6v1.6H11.4zm3.2 0h1.6v1.6h-1.6zM5.8 7.4h1.6v1.6H5.8zm3.2 0h1.6v1.6H9zm3.2 0h1.6v1.6h-1.6zm3.2 0h1.6v1.6h-1.6zM5.8 11.8h1.6v1.6H5.8zm3.2 0h1.6v1.6H9zm3.2 0h1.6v1.6h-1.6zm3.2 0h1.6v1.6h-1.6z"/></svg>');
	}

	.alipay-icon {
		background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%231677ff" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>');
	}

	.bank-icon {
		background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%23ff6600" d="M19 6h-2c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-9 12H9v-2h1v-2h-1v-2h1v-2h-2V8h6v8h-2zm7-4h-2v2h2v-2z"/></svg>');
	}

	.payment-details {
		background-color: #fff;
		border-radius: 10px;
		padding: 15px;
		margin-bottom: 20px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
	}

	.qrcode-container {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 20px;
	}

	.qrcode-image {
		width: 250px;
		height: 250px;
		background-color: #f5f5f5;
		margin-bottom: 15px;
		border: 1px dashed #ddd;
	}

	.qrcode-tip {
		color: #666;
		font-size: 14px;
	}

	.bank-form {
		margin-top: 10px;
	}

	.form-item {
		margin-bottom: 15px;
	}

	.form-row {
		display: flex;
		justify-content: space-between;
	}

	.form-label {
		display: block;
		margin-bottom: 5px;
		font-size: 14px;
		color: #333;
	}

	.form-input {
		width: 80%;
		height: 44px;
		padding: 0 15px;
		border: 1px solid #ddd;
		border-radius: 8px;
		font-size: 16px;
	}

	.bank-selector {
		margin-top: 15px;
		padding-top: 15px;
		border-top: 1px solid #eee;
	}

	.bank-list {
		width: 100%;
		white-space: nowrap;
		margin-top: 10px;
	}

	.bank-card {
		display: inline-block;
		width: 80px;
		height: 80px;
		margin-right: 15px;
		border: 1px solid #ddd;
		border-radius: 8px;
		padding: 8px;
		text-align: center;
		transition: all 0.3s;
	}

	.bank-card.active {
		border-color: #ff6600;
		background: #fff8f0;
	}

	.bank-icon {
		width: 40px;
		height: 40px;
		display: block;
	}

	.bank-name {
		font-size: 12px;
		display: block;
		margin-top: 5px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.pay-btn {
		width: 100%;
		padding: 15px;
		background: #ff6600;
		color: white;
		border: none;
		border-radius: 8px;
		font-size: 16px;
		transition: background 0.3s;
	}

	.pay-btn:disabled {
		background: #ccc;
	}

	.upload-section {
		background-color: #fff;
		border-radius: 10px;
		padding: 15px;
		margin-top: 20px;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
	}

	.upload-area {
		border: 2px dashed #ddd;
		border-radius: 8px;
		padding: 30px;
		text-align: center;
		transition: border-color 0.3s;
	}

	.upload-icon {
		font-size: 40px;
		color: #ff6600;
		margin-bottom: 10px;
	}

	.upload-tip {
		color: #999;
		font-size: 12px;
		display: block;
		margin-top: 5px;
	}

	.preview-container {
		margin-top: 15px;
		white-space: nowrap;
	}

	.image-wrapper {
		display: inline-block;
		position: relative;
		margin-right: 10px;
	}

	.preview-image {
		width: 100px;
		height: 100px;
		border-radius: 5px;
	}

	.delete-btn {
		position: absolute;
		top: -8px;
		right: -8px;
		width: 20px;
		height: 20px;
		background: #ff3b30;
		color: white;
		border-radius: 50%;
		text-align: center;
		line-height: 18px;
		font-size: 18px;
	}

	.submit-upload {
		width: 100%;
		padding: 12px;
		background: #4CAF50;
		color: white;
		border: none;
		border-radius: 5px;
		margin-top: 15px;
		font-size: 16px;
	}
	
	.tips {
		padding:20rpx 0;
		font-size:24rpx;
		color:#666;
	}
</style>