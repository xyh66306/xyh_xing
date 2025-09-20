<template>
	<view>
		<view class="list">
			<view class="item u-border-bottom" v-for="(vo,index) in list" :key="index">
				<view class="icon">
					<template v-if="vo.type==1">
						<u-avatar fontSize="14"text="转账" shape="square" randomBgColor></u-avatar>
					</template>
					<template v-else-if="vo.type==2">
						<u-avatar fontSize="14" text="充值" shape="square" randomBgColor></u-avatar>
					</template>										
					<template v-else-if="vo.type==3">
						<u-avatar fontSize="14" text="提现" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==4">
						<u-avatar fontSize="14" text="获赠" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==5">
						<u-avatar fontSize="14" text="返佣" shape="square" randomBgColor></u-avatar>
					</template>	
					<template v-else-if="vo.type==6">
						<u-avatar fontSize="14" text="冻结" shape="square" randomBgColor></u-avatar>
					</template>	
					<template v-else-if="vo.type==7">
						<u-avatar fontSize="14" text="兑出" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==8">
						<u-avatar fontSize="14" text="兑入" shape="square" randomBgColor></u-avatar>
					</template>										
				</view>
				<view class="info">
					<view class="time u-info">
						<template v-if="vo.flow_type==1">
							 <u--text type="primary" :text="vo.usdt"></u--text>
						</template>
						<template v-else>
							<u--text type="warning" :text="-vo.usdt"></u--text>
						</template>
					</view>
					<view class="">{{vo.createtime}}</view>
				</view>
				<view class="money">
					<view class="change u-primary">

					</view>
					<view class="yue">余额: {{vo.after}}</view>
				</view>
			</view>
<!-- 			<view class="item u-border-bottom">
				<view class="icon">
					<u-avatar text="卖" randomBgColor></u-avatar>
				</view>
				<view class="info">
					<view class="time u-info">2024-01-01 00:00:00</view>
					<view class="">C1234567890</view>
				</view>
				<view class="money">
					<view class="change u-error">-123.0000</view>
					<view class="yue">余额 1234.0000</view>
				</view>
			</view>
			<view class="item u-border-bottom">
				<view class="icon">
					<u-avatar text="转" randomBgColor></u-avatar>
				</view>
				<view class="info">
					<view class="time u-info">2024-01-01 00:00:00</view>
					<view class="">转出：李四</view>
				</view>
				<view class="money">
					<view class="change u-error">-123.0000</view>
					<view class="yue">余额 1234.0000</view>
				</view>
			</view>
			<view class="item u-border-bottom">
				<view class="icon">
					<u-avatar text="转" randomBgColor></u-avatar>
				</view>
				<view class="info">
					<view class="time u-info">2024-01-01 00:00:00</view>
					<view class="">转入：张三</view>
				</view>
				<view class="money">
					<view class="change u-primary">+123.0000</view>
					<view class="yue">余额 1234.0000</view>
				</view>
			</view>
			<view class="item u-border-bottom">
				<view class="icon">
					<u-avatar text="充" randomBgColor></u-avatar>
				</view>
				<view class="info">
					<view class="time u-info">2024-01-01 00:00:00</view>
					<view class=""> </view>
				</view>
				<view class="money">
					<view class="change u-primary">+123.0000</view>
					<view class="yue">余额 1234.0000</view>
				</view>
			</view> -->
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				page:1,
				type:'',
				flow_type:'',
				loadStatus: 'more',
				list:[],
				randomBgColor:''
			}
		},
		onLoad() {
			this.getUsdtLog();
		},
		onReachBottom() {
			if (this.loadStatus === 'more') {
				this.getUsdtLog();
			}
		},		
		mounted() {
			this.randomBgColor = this.getRandomColor(); // 在组件挂载后设置随机颜色
		},		
		methods: {
			getRandomColor() {
			  const letters = '0123456789ABCDEF';
			  let color = '#';
			  for (let i = 0; i < 6; i++) {
				color += letters[Math.floor(Math.random() * 16)];
			  }
			  return color;
			},			
			getUsdtLog(){
				uni.$u.http.post("/api/user/getusdtlog",{
					page:this.page,
					type:this.type,
					flow_type:this.flow_type,
				}).then((res)=>{
					if(res.code == 1) {
						const _list = res.data.list;
						this.list = [...this.list, ..._list];
						if (res.data.count > this.list.length) {
							this.loadStatus = 'more';
							this.page++;
						} else {
							// 数据已加载完毕
							this.loadStatus = 'noMore';
						}
					}else{
						uni.$u.toast(res.msg);
					}
				}).catch(res => {
					uni.$u.toast(res.msg);
				});
			}
		}
	}
</script>

<style lang="scss">
	.list {}

	.item {
		padding: 20rpx 30rpx;
		display: flex;
		align-items: center;
	}

	.icon {
		margin-right: 20rpx;
	}

	.info {
		flex: 1;
		font-size: 14px;
		line-height: 20px;
	}

	.time {
		flex: 1;
	}

	.change {
		font-size: 14px;
		margin-bottom: 5rpx;
		display: flex;
		justify-content: flex-end;
	}

	.yue {
		font-size: 14px;
	}
</style>