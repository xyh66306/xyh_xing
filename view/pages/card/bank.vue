<template>		
	<view class="wrap">
		<view class="form">			
			<view class="item">
				<view class="left">银行账户：</view>	
				<view class="right">
					<input type="text" v-model="bank_nums" focus @blur="checkCard()" class="input" placeholder="请输入银行账户"  placeholder-class="placeholder"/>
				</view>
			</view>	
			<view class="item">
				<view class="left">姓名：</view>	
				<view class="right">
					<input type="text" v-model="name" class="input" placeholder="请输入姓名" placeholder-class="placeholder"/>
				</view>
			</view>				
			<view class="item">
				<view class="left">银行名称：</view>	
				<view class="right">
					<input type="text" v-model="bank_name" class="input" placeholder="请输入银行名称"  placeholder-class="placeholder"/>
					
				</view>
			</view>				
			<view class="item">
				<view class="left">支行名称：</view>	
				<view class="right">
					<input type="text" v-model="bank_zhmc" class="input" placeholder="请输入银行支行名称"  placeholder-class="placeholder"/>
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
		<u-button type="primary" @click="submit" :disabled="disabled" shape="circle">确认提交</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id:'',
				name:'',
				type:1, //1CNY 2THB 3INR
				bank_name:'',
				bank_nums:'',
				bank_zhmc:'',
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
				disabled:false,
			}
		},
		onLoad(e) {
			if(e.id){
				this.id = e.id
				this.getInfo(e.id);
			}
		},
		methods:{
			// 判断获取银行卡类型
			checkCard () {
				if (this.bank_nums) {
					let data = {
						card_code: this.bank_nums
					}
					uni.$u.http.post('/api/bank/index',data).then(res => {
						if(res.code == 1) {
							this.bank_name = res.data.name
						}else{
							this.disabled = true;
							return uni.$u.toast(res.msg);
						}
					})	
				}
			},			
			  radioChange(n) {
				console.log('radioChange', n);
			  },			
			getInfo(id){
				uni.$u.http.post('/api/user/getbankcard', {
					id
				}).then(res => {
					if(res.code == 1) {
						this.name = res.data.username
						this.bank_name = res.data.bank_name
						this.bank_nums = res.data.bank_nums
						this.bank_zhmc = res.data.bank_zhmc
						this.radiovalue1 = res.data.status
					}
				})	
			},
			submit(){
				if(!this.name){
					return uni.$u.toast("请输入姓名");
				}
				if(!this.bank_name){
					return uni.$u.toast("请输入银行名称");
				}
				if(!this.bank_nums){
					return uni.$u.toast("请输入银行账户");
				}
				if(!this.bank_zhmc){
					return uni.$u.toast("请输入支行名称");
				}																			
				uni.$u.http.post('/api/user/addbankcard', {
					id:this.id,
					name: this.name,
					type:this.type,
					bank_name: this.bank_name,
					bank_nums: this.bank_nums,
					bank_zhmc:this.bank_zhmc,
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
			flex:0 0 160rpx;
		}
		.right {
			flex:0 0 530rpx;
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
