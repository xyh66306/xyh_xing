<template>
	<view>
		<view class="tab">
			<view class="tr">
				<view class="td">团队ID</view>
				<view class="td">团队名称</view>
				<view class="td">操作</view>
			</view>
			<view class="tr items" v-for="(vo,index) in teamLst" :key="index">
				<view class="td">{{vo.teamid}}</view>
				<view class="td">{{vo.name}}</view>
				<view class="td caozuo flex">
					<view class="set czbtn" @click="open(vo.team_user_id,vo.name)">设置</view>
					<view class="edit czbtn" @click="editTeam(vo.id)">编辑</view>
					<view class="del czbtn" @click="delTeam(vo.id)">删除</view>
				</view>
			</view>
		</view>

		<view class="addArea">
			<u-button type="primary" shape="circle" @click="addTeam">添加团队</u-button>
		</view>

		<u-popup :show="teamSetShow" mode="center" @close="close">
			<view class="popArea">
				<view class="pop_header">
					{{setTeamName}}返佣设置
				</view>
				<view class="select">
					<view class="pop_tit">
						支付类型
					</view>
					<view class="pop_item">
						<u-radio-group v-model="radiovalue1" placement="row">
							<u-radio :customStyle="{marginLeft: '8px'}" v-for="(item, index) in radiolist1" :key="index"
								:label="item.name" :name="item.name" @change="radioChange1">
							</u-radio>
						</u-radio-group>
					</view>
				</view>
				<view class="select">
					<view class="pop_tit">
						资金流向
					</view>
					<view class="pop_item">
						<u-radio-group v-model="radiovalue2" placement="row">
							<u-radio :customStyle="{marginLeft: '8px'}" v-for="(item, index) in radiolist2" :key="index"
								:label="item.name" :name="item.name" @change="radioChange2">
							</u-radio>
						</u-radio-group>
					</view>
				</view>
				<view class="select">
					<view class="pop_tit">
						返佣费率
					</view>
					<view class="pop_item">							
						  <u--input
						    placeholder="请输入返佣费率(%)"
						    border="bottom"
						    v-model="rate"
						  ></u--input>	
					</view>
				</view>
				<view class="btnArea">
					<u-button type="info" @click="close" style="margin-right: 15rpx;">取消</u-button>
					<u-button type="primary" @click="setTeam" style="margin-left: 15rpx;">确认</u-button>
				</view>
			</view>
		</u-popup>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				radiolist1: [{
						name: '银行卡',
						value: 'bank'
					},
					{
						name: '二维码',
						value: 'ewm'
					},
				],
				radiolist2: [{
						name: '兑出',
						value: 'duichu'
					},
					{
						name: '兑入',
						value: 'duiru'
					},
				],
				rate:'',
				radiovalue1: '银行卡',
				radiovalue2: '兑出',
				teamSetShow: false,
				setId: '',
				setTeamName: '',
				teamLst: [],
			}
		},
		onShow() {
			this.getTeam();
		},
		methods: {
			radioChange1(n) {
				console.log('radioChange', n);
				this.radiovalue1 = n
			},
			radioChange2(n) {
				console.log('radioChange', n);
				this.radiovalue2 = n
			},
			open(id, name) {
				this.setId = id;
				this.setTeamName = name
				this.teamSetShow = true;
				// console.log('open');
			},
			close() {
				this.teamSetShow = false
				// console.log('close');
			},
			getTeam() {
				uni.$u.http.post('/api/team/getTeam').then(res => {
					this.teamLst = res.data;
				}).catch(res => {
					console.log(res)
				});
			},
			addTeam() {
				uni.navigateTo({
					url: "/pages/company/group/add"
				})
			},
			editTeam(id) {
				uni.navigateTo({
					url: "/pages/company/group/add?id=" + id
				})
			},
			setTeam() {
				this.close();
				let type = ''
				let that =this
				if(this.radiovalue1 =='银行卡'){
					type = 'bank'
				} else {
					type = 'ewm'
				}
				let churu = ''
				if(this.radiovalue2 =='兑出'){
					churu = 'duichu'
				} else {
					churu = 'duiru'
				}	
							
				uni.$u.http.post('/api/rebate/settingGroup',{
					pid:that.setId,
					churu:churu,
					type:type,
					rate:that.rate,
				}).then(res => {
					if (res.code == 1) {
						uni.$u.toast("已设置");
						that.getTeam();
						that.rate = '';
					} else {
						uni.$u.toast(res.msg);
					}

				}).catch(res => {
					console.log(res)
				});
			},
			delTeam(id) {
				uni.showModal({
					title: "确认删除吗",
					success: function(res) {
						if (res.confirm) {
							uni.$u.http.post('/api/team/delTeam').then(res => {
								if (res.code == 1) {
									uni.$u.toast("已删除");
									this.getTeam();
								} else {
									uni.$u.toast(res.msg);
								}

							}).catch(res => {
								console.log(res)
							});
						} else if (res.cancel) {
							// 用户取消操作
						}
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.tab {
		// width:710rpx;
		// margin: 0 auto;
		background: #fff;

		.tr {
			display: flex;
			height: 70rpx;
			line-height: 70rpx;

			.td {
				flex: 1;
				text-align: center;
				border-bottom: #f3f3f3 2rpx solid;
				font-size: 28rpx;
			}
		}

		.items {
			font-size: 24rpx;

			.caozuo {
				display: flex;
				justify-content: center;

				.czbtn {
					margin-right: 16rpx;
				}
			}
		}
	}

	.addArea {
		width: 710rpx;
		position: fixed;
		bottom: 4rpx;
		left: 20rpx;
	}

	.popArea {
		width: 650rpx;
		// height:350rpx;
		padding: 30rpx;
	}

	.pop_header {
		padding-bottom: 20rpx;
		text-align: center;
		font-size: 32rpx;
	}

	.popArea .select {
		display: flex;
		height: 80rpx;
		align-content: center;

		.pop_tit {
			line-height: 80rpx;
			margin-right: 20rpx;
			font-size: 28rpx;
		}

		.pop_item {
			display: flex;
			align-content: center;
		}
	}

	.popInput {
		width: 100%;
		height: 70rpx;
		border: #e9e9e9 2rpx solid;
		padding-left: 10rpx;
		color: #333;
		font-size: 28rpx;
	}

	.placeholder {
		padding-left: 10rpx;
		color: #e9e9e9;
		font-size: 28rpx;
	}

	.btnArea {
		display: flex;
		justify-content: space-between;
		margin-top: 20rpx;
		// border-top:#e9e9e9 2rpx solid;
		padding-top: 20rpx;
	}
</style>