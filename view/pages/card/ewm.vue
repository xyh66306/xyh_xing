<template>		
	<view class="wrap">
		<view class="form">
			<view class="item">
				<view class="left">姓名：</view>	
				<view class="right">
					<input type="text" v-model="name" class="input" placeholder="请输入姓名" placeholder-class="placeholder"/>
				</view>
			</view>		
			<view class="item">
				<view class="left">账户：</view>	
				<view class="right">
					<input type="text" v-model="pay_nums" class="input" placeholder="请输入平台账户"  placeholder-class="placeholder"/>
				</view>
			</view>	
			<view class="item">
				<view class="left">类型：</view>	
				<view class="right">
					<input type="text" :value="pay_skpt==2?'支付宝':'微信'" class="input" disabled placeholder-class="placeholder"/>
				</view>
			</view>						
			<view class="item">
				<view class="left">二维码：</view>	
				<view class="right">
					<u-upload :fileList="pay_ewm" @afterRead="afterRead" @delete="deletePic" :maxCount="1" name="pay_ewm"
						width="150" height="150" uploadText="点击上传收款二维码"></u-upload>
				</view>
			</view>	
			<view class="item">
				<view class="left">状态：</view>	
				<view class="right item-center">
					  <u-radio-group
					    v-model="radiovalue1"
						shape='square'
						placement="row"
					  >
					    <u-radio
					      :customStyle="{marginLeft: '5px'}"
					      v-for="(item, index) in radiolist1"
					      :key="index"
					      :label="item.name"
					      :name="item.value"
					      @change="radioChange"
					    >
					    </u-radio>
					  </u-radio-group>
				</view>
			</view>				
		</view>
		<u-button type="primary" @click="submit" shape="circle">确认提交</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id:'',
				name:'',
				pay_skpt:1, //1微信2	支付宝
				type:'CNY', //1CNY 2THB 3INR
				pay_nums:'',
				shuoming:'',
				beizhu:'',
				pay_ewm:[],
				pay_ewm_txt:'',
				radiolist1: [
					  {
						name: '启用',
						value:'normal',						
						disabled: false
					  },
					{
					  name: '禁用',
					  value:'hidden',
					  disabled: false
					}
				],
				radiovalue1: 'normal',				
			}
		},
		onLoad(e) {
			if(e.pay_skpt){
				this.pay_skpt = e.pay_skpt
				this.getInfo();
			}
		},
		methods:{	
			  radioChange(n) {
				console.log('radioChange', n);
			  },			
			getInfo(){
				uni.$u.http.post('/api/user/getpayewm', {
					pay_skpt: this.pay_skpt==1?'wxpay':'alipay',
				}).then(res => {
					if(res.code == 1) {
						this.id = res.data.id
						this.name = res.data.username
						this.pay_nums = res.data.pay_nums
						this.shuoming = res.data.shuoming
						this.beizhu = res.data.beizhu
						this.pay_ewm_txt = res.data.pay_ewm_image
						this.pay_ewm[0] = res.data.pay_ewm_image
						this.radiovalue1 = res.data.status
					}
				})	
			},
			submit(){
				if(!this.name){
					return uni.$u.toast("请输入姓名");
				}
				if(!this.pay_nums){
					return uni.$u.toast("请输入收款账户");
				}
				if(!this.pay_ewm_txt){
					return uni.$u.toast("请上传收款码");
				}											
				uni.$u.http.post('/api/user/addpayewm', {
					id:this.id,
					name: this.name,
					pay_skpt: this.pay_skpt,
					type: this.type,
					pay_nums:this.pay_nums,
					shuoming:this.shuoming,
					beizhu:this.beizhu,
					pay_ewm:this.pay_ewm_txt,
					status:this.radiovalue1
				}).then(res => {
					if(res.code == 1) {
						uni.showModal({
							content: '绑定成功',
							showCancel: false,
							success: () => {
								uni.navigateBack();
							}
						})
					}else{
						uni.$u.toast(res.msg);
					}
				})				
			},
			// 新增图片
			async afterRead(event) {
				let lists = [].concat(event.file);
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
						this[event.name].splice(0, 1, Object.assign(item, {
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
				});
			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.pay_ewm_txt = '';
			},
		}
	}	
</script>

<style scoped lang="scss">
page {
	background-color: #fff;
}	
.wrap {
	padding: 30rpx;
	.item {
		display:flex;
		min-height:120rpx;
		line-height:120rpx;
		border-bottom:#f5f5f5 2rpx solid;
		.left {
			flex:0 0 120rpx;
		}
		.right {
			flex:0 0 570rpx;
			padding:10rpx;
			.input {
				width:100%;
				height:100%;
				font-size:28rpx;
				color:#000;
			}
			.placeholder{
				font-size:28rpx;
				color:#999;
			}
		}
	}
}

.form {
	margin-bottom: 80rpx;
}

.agreement {
	display: flex;
	align-items: center;
	margin: 40rpx 0;

	.agreement-text {
		padding-left: 8rpx;
		color: $u-tips-color;
	}
}
.item-center {
	display: flex;
	justify-content: center;
	align-items: center;
}
</style>
