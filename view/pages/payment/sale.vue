<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">订单ID</view>
			<view class="flex u-border-bottom" @click="copyText(details.orderid)">
				<view>{{details.orderid}}</view>
			</view>		
			<template  v-if="details.payername">
				<view class="u-info">付款人</view>
				<view class="flex u-border-bottom">
					<view>{{details.payername}}</view>
				</view>	
			</template>			
			<view class="u-info">订单金额</view>
			<view class="flex u-border-bottom">
				<view>{{details.amount}}</view>
			</view>		
			<view class="u-info">交易数量</view>
			<view class="flex u-border-bottom">
				<view>{{details.user_usdt}}</view>
			</view>													
			<template v-if="details.pay_type=='bank'">
				<view class="u-info">付款账户</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_account}}</view>
				</view>
				<view class="u-info">付款银行</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_name}}</view>
				</view>
				<view class="u-info">支行名称</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_zhihang}}</view>
				</view>
			</template>
			<template v-else>
				<view class="u-info">付款账户</view>
				<view class="flex u-border-bottom">
					<view>{{details.pay_account}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.pay_account)">复制</u-button>
					</view>
				</view>
				<view class="u-info">付款类型</view>
				<view class="flex u-border-bottom">
					<view>
						<template v-if="details.pay_type==1">银行卡</template>
						<template v-if="details.pay_type==2">支付宝</template>
						<template v-if="details.pay_type==3">微信</template>
					
					</view>
				</view>
			
			</template>
			<view class="u-info">凭证</view>
			<view class="flex u-border-bottom">
				<image :src="vo" v-for="(vo,index) in details.pinzheng_image_arr" :key="index"  @click="previewImage(index)" class="pay_ewm_img"></image>
			</view>					
			<template v-if="details.ctime"> 
				<view class="u-info">发起时间</view>
				<view class="flex u-border-bottom">
					<view>{{details.ctime_text}}</view>
				</view>	
			</template>
		
			<view class="tips">
				<view>【禁止备注】禁止备注任何敏感字样</view>
				<view>【禁止他人代付】付款人必须是交易账户持有人</view>
			</view>
		</view>

		<template v-if="details.pay_status<=2">
			<view class="btnlst">
				<u-button type="primary" :plain="true" @click="cancel()" style="width:45%">订单申诉</u-button>
				<u-button type="primary" @click="submit()" style="width:45%">订单确认</u-button>
			</view>
		</template>	
		<template v-if="details.pay_status==3">
			<u-button type="primary" :disabled="true">待平台审核</u-button>
		</template>		
		<template v-else-if="details.pay_status==4">
			<u-button type="primary" :disabled="true" >订单确认</u-button>
		</template>				
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show: false,
				details:{},
				pay_status:'',
				orderid:'',
				pay_ewm:[],
				pay_ewm_txt:'',
				pay_sub:false,
				token:'',
			}
		},
		onLoad(e) {
			this.orderid = e.orderid
			this.getPayDetails();
		},
		methods: {
			previewImage(index) {
			  const urls = this.details.pinzheng_image_arr
			  uni.previewImage({
				current: index,
				urls: urls
			  });
			},			
			open() {
			  // this.show = true
			},
			close() {
			  this.show = false
			},
			copyText(text) {
			  // 检查 Clipboard API 是否可用
			  var that = this;
			  if (navigator.clipboard) {
				navigator.clipboard.writeText(text).then(() => {
				  uni.$u.toast("文本已复制到剪贴板");
				}, (err) => {
					uni.$u.toast("复制失败");
				});
			  } else {
				// 兼容旧浏览器，使用旧的 execCommand 方法
				const el = document.createElement('textarea');
				el.value = text;
				el.setAttribute('readonly', '');
				el.style.position = 'absolute';
				el.style.left = '-9999px';
				document.body.appendChild(el);
				el.select();
				document.execCommand('copy');
				document.body.removeChild(el);
				uni.$u.toast("文本已复制到剪贴板");
			  }
			},				
			cancel(){
				let that = this;				
				uni.showModal({
					title: '取消支付订单！',
					icon: 'none',
					duration: 1000,
					showCancel: true, // 是否显示取消按钮（默认true）
					cancelText: '取消', // 取消按钮文字（默认"取消"）
					cancelColor: '#999', // 取消按钮文字颜色（默认#000）
					confirmText: '确定', // 确认按钮文字（默认"确定"）
					confirmColor: '#007AFF', // 确认按钮颜色（默认#3CC51F）
					success: function(res) {
						if (res.confirm) {
							uni.$u.http.post('/api/rujin/nopayorder', {
								orderid:that.orderid,
								auth_token:that.details.authtoken
							}).then(res => {
								if (res.code == 1) {
									that.getPayDetails();
									uni.$u.toast(res.msg);
								} else {
									uni.$u.toast(res.msg);
								}
							
							}).catch(res => {
								console.log(res)
							});
						}
					}	
				})	
			},		
			submit(){
				let that = this;
				if(this.pay_sub){
					return;
				}
				
				uni.showModal({
					title: '确认支付订单！',
					icon: 'none',
					duration: 1000,
					showCancel: true, // 是否显示取消按钮（默认true）
					cancelText: '取消', // 取消按钮文字（默认"取消"）
					cancelColor: '#999', // 取消按钮文字颜色（默认#000）
					confirmText: '确定', // 确认按钮文字（默认"确定"）
					confirmColor: '#007AFF', // 确认按钮颜色（默认#3CC51F）
					success: function(res) {
						if (res.confirm) {
							that.pay_sub = true
							uni.$u.http.post('/api/rujin/payorder', {
								orderid:that.orderid,
								auth_token:that.details.authtoken
							}).then(res => {
								if (res.code == 1) {
									that.getPayDetails();
									uni.$u.toast(res.msg);
								} else {
									uni.$u.toast(res.msg);
								}
							
							}).catch(res => {
								console.log(res)
							});
						}
					}	
				})	
			},
			getPayDetails(){
				let that = this
				uni.$u.http.post('/api/rujin/detail', {
					orderid: this.orderid,
				}).then(res => {
					if (res.code == 1) {
						that.details = res.data
					}				
				}).catch(res => {
					// console.log(res)
				});
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding:20rpx;
	}

	.payinfo {
		padding: 30rpx;
		background-color: #fff;
		border-radius: 10rpx;
		margin-bottom: 30rpx;

		.flex {
			display: flex;
			align-items: center;
			padding: 20rpx 0;
			margin-bottom: 30rpx;

			.copy {
				margin-left: auto;
			}
		}

		.tips {
			font-size: 14px;
			color: $u-error;
		}
	}
	.pay_ewm_img {
		width:230rpx;
		height:230rpx;
		margin: 0 10rpx;
	}
	.popArea {
		padding-top:50rpx;
		.pop_head {
			padding:0 20rpx;
			text-align:center;
			font-size:32rpx;
		}
		.upload {
			width:300rpx;
			height:300rpx;
			margin:0 auto;
		}
	}
	.btnlst {
		display:flex;
		justify-content: space-between;
	}
</style>