<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">发起者</view>
			<view class="flex u-border-bottom">
				<view>{{details.realName}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.realName)">复制</u-button>
				</view>
			</view>
			<view class="u-info">订单ID</view>
			<view class="flex u-border-bottom">
				<view>{{orderid}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(orderid)">复制</u-button>
				</view>
			</view>		
			<view class="u-info">实际订单数量</view>
			<view class="flex u-border-bottom">
				<view>{{details.user_usdt}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.user_usdt)">复制</u-button>
				</view>
			</view>	
			<view class="u-info">应支付</view>
			<view class="flex u-border-bottom">
				<view>{{details.withdrawAmount}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.withdrawAmount)">复制</u-button>
				</view>
			</view>	
			<view class="u-info">汇率</view>
			<view class="flex u-border-bottom">
				<view>{{biData.duichu || '7.20' }}</view>
			</view>											
			<template v-if="details.pay_type=='bank'">
				<view class="u-info">付款账户</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_account}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_account)">复制</u-button>
					</view>
				</view>
				<view class="u-info">付款银行</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_name}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_name)">复制</u-button>
					</view>
				</view>
				<view class="u-info">支行名称</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_zhihang}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_zhihang)">复制</u-button>
					</view>
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
						<template v-if="details.pay_type=='bank'">银行卡</template>
						<template v-if="details.pay_type=='alipay'">支付宝</template>
						<template v-if="details.pay_type=='wxpay'">微信</template>
					
					</view>
				</view>			
			</template>	
		
			<template v-if="details.pay_ewm_image_arr.length>0">
				<view class="u-info">付款凭证</view>
				<view class="flex u-border-bottom">
					<view>
						<image :src="vo" v-for="(vo,index) in details.pay_ewm_image_arr" :key="index"  @click="previewImage(index)" class="pay_ewm_img"></image>
					</view>
				</view>		
			</template>		
			<template v-if="details.ctime"> 
				<view class="u-info">发起时间</view>
				<view class="flex u-border-bottom">
					<view>{{details.ctime}}</view>
				</view>	
			</template>
		
			<view class="tips">
				<view>【禁止备注】禁止备注任何敏感字样</view>
				<view>【禁止他人代付】付款人必须是交易账户持有人</view>
			</view>
		</view>
		<template v-if="pay_status<=1">
			<u-button type="primary" @click="addOrder()">立即抢单</u-button>
		</template>
		<template v-else>
			<template v-if="userInfo.id !=details.user_id">
				<u-button type="primary" disabled>已被抢单</u-button>
			</template>
			<template v-else>
				<template v-if="pay_status==2">
					<u-button type="primary" @click="show = true">立即付款</u-button>
				</template>	
				<template v-if="pay_status==3">
					<u-button type="primary" @click="show = true">已支付，待审核</u-button>
				</template>		
				<template v-if="pay_status==4">
					<u-button type="primary" @click="show = true">商户已审核</u-button>
				</template>				
				<template v-if="pay_status==5">
					<u-button type="primary">已完成</u-button>
				</template>	
				<template v-if="pay_status==6">
					<u-button type="primary" disabled>取消</u-button>
				</template>	
			</template>			
			
		</template>


		
		<u-popup :show="show" @close="close" @open="open">
			<view class="popArea">
				<!-- <view class="pop_head">立即付款</view> -->
				<view class="upload">
					<u-upload :fileList="pay_ewm" @afterRead="afterRead" :previewImage="true" :previewFullImage="true" @delete="deletePic" :maxCount="3" name="pay_ewm"
						width="100" height="100" uploadText="点击上传支付凭证"></u-upload>
				</view>
				<view class="btn">
					<u-button type="primary" @click="updatePayImg()">立即上传</u-button>
				</view>
			</view>

		</u-popup>
					
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show: false,
				details:{
					user_id:0,
					realName:'',
					usdt:'',
					user_usdt:'',
					pay_type:1, 
					bank_account:'',
					bank_name:'',
					bank_zhihang:'',
					pay_account:'',
					pay_ewm_image:'',
					rate:'',
					withdrawAmount:0,
					ctime:'',
					pay_ewm_image_arr:[],
					authtoken:'',
				},
				pay_status:'',
				orderid:'',
				pay_ewm:[],
				pay_ewm_txt:'',
				userInfo:{},
				biData:{},
				pay_sub:false,
			}
		},
		onLoad(e) {
			this.userInfo = uni.getStorageSync('user') || {};
			this.orderid = e.orderid
			this.detail();
			this.getBiLst();
		},
		methods: {
			previewImage(index) {
			  const urls = this.details.pay_ewm_image_arr
			  uni.previewImage({
				current: 1,
				urls: urls
			  });
			},				
			getBiLst(){
				uni.$u.http.post('/api/index/getBiLst', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						res.data.forEach(res=>{
							if(res.default==1){
								this.biData = res;
							}
						})
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},			
			copy(text){
				uni.setClipboardData({
					data: text,
					success: () => {
						uni.$u.toast('已复制');
					}
				})
			},
			open() {
			  // this.show = true
			},
			close() {
			  this.show = false
			},			
			addOrder(){
				let that = this;
				
				if(this.pay_sub){
					return;
				}				
				// let data = this.details;
				// data.orderid = this.orderid
				uni.showModal({
					title: '确认抢单！',
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
							uni.$u.http.post('/api/chujin/addCash', {
								orderid:that.orderid,
								auth_token:that.details.authtoken
							}).then(res => {
								if (res.code == 1) {
									that.pay_sub = false
									that.detail();									
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
			detail(){
				let that = this
				uni.$u.http.post('/api/chujin/detail', {
					orderid: this.orderid,
				}).then(res => {
					if (res.code == 1) {
						let data = res.data;
						that.pay_status = data.pay_status
						that.details = {
							user_id:data.user_id,
							realName:data.realName,
							pay_type:data.pay_type, 
							bank_account:data.cardNumber, 
							bank_name:data.bankName, 
							bank_zhihang:data.bankBranchName, 
							user_usdt:data.user_usdt,
							usdt:data.usdt,
							huilv:data.huilv,
							withdrawAmount:data.withdrawAmount, 
							pay_ewm_image:data.pay_ewm_image,
							pay_ewm_image_arr:data.pay_ewm_image_arr,
							ctime:data.ctime, 
							authtoken:data.authtoken
						}
						if(data.pinzheng_image){
							that.pay_ewm.push(data.pinzheng_image);
						}
					}				
				}).catch(res => {
					// console.log(res)
				});
			},
			// 新增图片
			async afterRead(event) {
				/*let lists = [].concat(event.file);
				let fileListLen  = this[`fileList${event.name}`].length;
				let item = {
					...event.file,
					status: 'uploading',
					message: '上传中'
				};
				this[event.name] = [].concat(item);
				const res = await uni.$u.http.upload('/api/common/upload', {
					filePath: event.file.url,
					name: 'file',
				}).then(res => {
					if (res?.code == 1) {						
						this[event.name].splice(fileListLen, 1, Object.assign(item, {
							status: 'success',
							message: '',
							url: res.data.fullurl
						}));
						this.pay_ewm_txt = res.data.url;
					} else {
						uni.$u.toast(res.msg || '上传失败');
						this[event.name] = [];
						this.pay_ewm_txt = '';
					}
				}).catch(res => {
					this[event.name] = [];
					this.pay_ewm_txt = '';
				});*/
				// 当设置 multiple 为 true 时, file 为数组格式，否则为对象格式
				let lists = [].concat(event.file);
				let fileListLen = this.pay_ewm.length;
				lists.map((item) => {
				 this.pay_ewm.push({
					...item,
					status: "uploading",
					message: "上传中",
				  });
				});
				for (let i = 0; i < lists.length; i++) {
				  const result = await this.uploadFilePromise(lists[i].url);
				  let item = this.pay_ewm[fileListLen];
				  this.pay_ewm.splice(
					fileListLen,
					1,
					Object.assign(item, {
					  status: "success",
					  message: "",
					  url: result,
					})
				  );
				  fileListLen++;
				}
				
			},
			uploadFilePromise(url){
				return new Promise((resolve, reject) => {
				  
					uni.$u.http.upload('/api/common/upload', {
						filePath:url,
						name: 'file',
					}).then(res => {
						  setTimeout(() => {
							resolve(res.data.fullurl);
						  }, 1000);
					})
				  
				});
				

			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.pay_ewm_txt = '';
			},
			updatePayImg(){
				let that =this;
				if(that.pay_ewm.length==0){
					uni.$u.toast("请上传图片");
				}
				let pay_ewm_arr = []
				for(var i=0;i<that.pay_ewm.length;i++){
					pay_ewm_arr[i] = that.pay_ewm[i].url
				}
				let pay_ewm_txt = pay_ewm_arr.toString()
				
				that.pay_sub = true
				let data = {
					orderid:this.orderid,
					pinzheng_image:pay_ewm_txt,
					auth_token:that.details.authtoken
				}
				uni.$u.http.post('/api/chujin/uploadPzImg', data).then(res => {
					if (res.code == 1) {
						that.detail();	
						uni.$u.toast(res.msg);
						that.close();
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
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
	}
	.popArea {
		padding-top:50rpx;
		.pop_head {
			padding:0 20rpx;
			text-align:center;
			font-size:32rpx;
		}
		.upload {
			padding:10rpx 20rpx;
			// width:300rpx;
			// height:300rpx;
			// margin:0 auto;
		}
	}
</style>