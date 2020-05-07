/*
 Navicat MySQL Data Transfer

 Source Server         : vm130
 Source Server Type    : MySQL
 Source Server Version : 50729
 Source Host           : localhost:3306
 Source Schema         : eschat

 Target Server Type    : MySQL
 Target Server Version : 50729
 File Encoding         : 65001

 Date: 06/05/2020 17:43:25
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for chat_record
-- ----------------------------
DROP TABLE IF EXISTS `chat_record`;
CREATE TABLE `chat_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `friend_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是群聊消息记录的话 此id为0',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '如果不为0说明是群聊',
  `content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '聊天内容',
  `time` int(11) UNSIGNED NOT NULL COMMENT '消息时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '聊天记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of chat_record
-- ----------------------------
INSERT INTO `chat_record` VALUES (19, 1, 3, 0, '天明，你好', 1588757707);
INSERT INTO `chat_record` VALUES (20, 3, 1, 0, '你好', 1588757744);
INSERT INTO `chat_record` VALUES (21, 3, 0, 10012, '明天上午10点开会，收到请回复', 1588757840);
INSERT INTO `chat_record` VALUES (22, 1, 0, 10012, '收到', 1588757848);

-- ----------------------------
-- Table structure for friend
-- ----------------------------
DROP TABLE IF EXISTS `friend`;
CREATE TABLE `friend`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `friend_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '好友id',
  `friend_group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '好友租id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '好友表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of friend
-- ----------------------------
INSERT INTO `friend` VALUES (1, 1, 2, 1);
INSERT INTO `friend` VALUES (2, 2, 1, 2);
INSERT INTO `friend` VALUES (3, 2, 3, 2);
INSERT INTO `friend` VALUES (4, 3, 2, 3);
INSERT INTO `friend` VALUES (5, 1, 3, 1);
INSERT INTO `friend` VALUES (6, 3, 1, 3);
INSERT INTO `friend` VALUES (7, 1, 4, 1);
INSERT INTO `friend` VALUES (8, 4, 1, 4);
INSERT INTO `friend` VALUES (9, 2, 4, 2);
INSERT INTO `friend` VALUES (10, 4, 2, 4);
INSERT INTO `friend` VALUES (11, 3, 4, 3);
INSERT INTO `friend` VALUES (12, 4, 3, 4);
INSERT INTO `friend` VALUES (13, 4, 5, 4);
INSERT INTO `friend` VALUES (14, 5, 4, 5);
INSERT INTO `friend` VALUES (15, 3, 5, 3);
INSERT INTO `friend` VALUES (16, 5, 3, 5);
INSERT INTO `friend` VALUES (17, 1, 5, 1);
INSERT INTO `friend` VALUES (18, 5, 1, 5);

-- ----------------------------
-- Table structure for friend_group
-- ----------------------------
DROP TABLE IF EXISTS `friend_group`;
CREATE TABLE `friend_group`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `groupname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '好友组名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '好友分组' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of friend_group
-- ----------------------------
INSERT INTO `friend_group` VALUES (1, 1, '默认分组');
INSERT INTO `friend_group` VALUES (2, 2, '默认分组');
INSERT INTO `friend_group` VALUES (3, 3, '默认分组');
INSERT INTO `friend_group` VALUES (4, 4, '默认分组');
INSERT INTO `friend_group` VALUES (5, 5, '默认分组');

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '群组所属用户id,群主',
  `groupname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '群名',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '群组头像',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10016 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '群组' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group
-- ----------------------------
INSERT INTO `group` VALUES (10011, 1, '测试交流群', '/Static/upload/5eb24bdf51cbcxiongmao.jpg');
INSERT INTO `group` VALUES (10012, 3, '公司内部交流群', '/Static/upload/5eb24de6559afgongsi.jpg');
INSERT INTO `group` VALUES (10013, 3, 'EasySwoole交流群', '/Static/upload/5eb24e3c80359es.jpg');
INSERT INTO `group` VALUES (10014, 3, 'XIAMEN PHPER CLUB', '/Static/upload/5eb24e5dcde0dcoffee.png');
INSERT INTO `group` VALUES (10015, 3, 'Go开发者乐园', '/Static/upload/5eb24e80ab985coding.png');

-- ----------------------------
-- Table structure for group_member
-- ----------------------------
DROP TABLE IF EXISTS `group_member`;
CREATE TABLE `group_member`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '群id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '群成员' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_member
-- ----------------------------
INSERT INTO `group_member` VALUES (1, 10001, 1);
INSERT INTO `group_member` VALUES (2, 10011, 1);
INSERT INTO `group_member` VALUES (3, 10001, 2);
INSERT INTO `group_member` VALUES (4, 10011, 2);
INSERT INTO `group_member` VALUES (5, 10001, 3);
INSERT INTO `group_member` VALUES (6, 10011, 3);
INSERT INTO `group_member` VALUES (7, 10012, 3);
INSERT INTO `group_member` VALUES (8, 10013, 3);
INSERT INTO `group_member` VALUES (9, 10014, 3);
INSERT INTO `group_member` VALUES (10, 10015, 3);
INSERT INTO `group_member` VALUES (11, 10012, 2);
INSERT INTO `group_member` VALUES (12, 10015, 2);
INSERT INTO `group_member` VALUES (13, 10014, 2);
INSERT INTO `group_member` VALUES (14, 10013, 2);
INSERT INTO `group_member` VALUES (15, 10014, 1);
INSERT INTO `group_member` VALUES (16, 10013, 1);
INSERT INTO `group_member` VALUES (17, 10012, 1);
INSERT INTO `group_member` VALUES (18, 10015, 1);
INSERT INTO `group_member` VALUES (19, 10001, 4);
INSERT INTO `group_member` VALUES (20, 10012, 4);
INSERT INTO `group_member` VALUES (21, 10011, 4);
INSERT INTO `group_member` VALUES (22, 10013, 4);
INSERT INTO `group_member` VALUES (23, 10001, 5);
INSERT INTO `group_member` VALUES (24, 10012, 5);
INSERT INTO `group_member` VALUES (25, 10011, 5);

-- ----------------------------
-- Table structure for offline_message
-- ----------------------------
DROP TABLE IF EXISTS `offline_message`;
CREATE TABLE `offline_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `data` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '数据',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0未发送 1已发送',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '离线消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of offline_message
-- ----------------------------
INSERT INTO `offline_message` VALUES (14, 3, '{\"type\":\"msgBox\",\"count\":1}', 1);
INSERT INTO `offline_message` VALUES (15, 2, '{\"type\":\"msgBox\",\"count\":1}', 1);
INSERT INTO `offline_message` VALUES (16, 1, '{\"type\":\"msgBox\",\"count\":1}', 1);
INSERT INTO `offline_message` VALUES (17, 3, '{\"type\":\"msgBox\",\"count\":1}', 1);
INSERT INTO `offline_message` VALUES (18, 2, '{\"type\":\"msgBox\",\"count\":1}', 0);
INSERT INTO `offline_message` VALUES (19, 2, '{\"username\":\"\\u5929\\u660e(3)\",\"avatar\":\"\\/Static\\/upload\\/5eb24d42d9244tianming.png\",\"id\":10012,\"type\":\"group\",\"content\":\"\\u660e\\u5929\\u4e0a\\u534810\\u70b9\\u5f00\\u4f1a\\uff0c\\u6536\\u5230\\u8bf7\\u56de\\u590d\",\"cid\":0,\"mine\":false,\"fromid\":3,\"timestamp\":1588757840000}', 0);
INSERT INTO `offline_message` VALUES (20, 5, '{\"username\":\"\\u5929\\u660e(3)\",\"avatar\":\"\\/Static\\/upload\\/5eb24d42d9244tianming.png\",\"id\":10012,\"type\":\"group\",\"content\":\"\\u660e\\u5929\\u4e0a\\u534810\\u70b9\\u5f00\\u4f1a\\uff0c\\u6536\\u5230\\u8bf7\\u56de\\u590d\",\"cid\":0,\"mine\":false,\"fromid\":3,\"timestamp\":1588757840000}', 1);
INSERT INTO `offline_message` VALUES (21, 2, '{\"username\":\"\\u7b56\\u9002\\u4f0a(1)\",\"avatar\":\"\\/Static\\/upload\\/5eb247df9703fxixi.jpg\",\"id\":10012,\"type\":\"group\",\"content\":\"\\u6536\\u5230\",\"cid\":0,\"mine\":false,\"fromid\":1,\"timestamp\":1588757848000}', 0);
INSERT INTO `offline_message` VALUES (22, 5, '{\"username\":\"\\u7b56\\u9002\\u4f0a(1)\",\"avatar\":\"\\/Static\\/upload\\/5eb247df9703fxixi.jpg\",\"id\":10012,\"type\":\"group\",\"content\":\"\\u6536\\u5230\",\"cid\":0,\"mine\":false,\"fromid\":1,\"timestamp\":1588757848000}', 0);

-- ----------------------------
-- Table structure for system_message
-- ----------------------------
DROP TABLE IF EXISTS `system_message`;
CREATE TABLE `system_message`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '接收用户id',
  `from_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源相关用户id',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '群id',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '添加好友附言',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0好友请求 1请求结果通知',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0未处理 1同意 2拒绝',
  `read` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0未读 1已读，用来显示消息盒子数量',
  `time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_message
-- ----------------------------
INSERT INTO `system_message` VALUES (7, 3, 4, 4, '', 0, 1, 1, 1588755243);
INSERT INTO `system_message` VALUES (8, 2, 4, 4, '', 0, 1, 1, 1588755245);
INSERT INTO `system_message` VALUES (9, 1, 4, 4, '', 0, 1, 1, 1588755247);
INSERT INTO `system_message` VALUES (10, 4, 1, 0, '', 1, 1, 1, 1588755278);
INSERT INTO `system_message` VALUES (11, 4, 2, 0, '', 1, 1, 1, 1588755302);
INSERT INTO `system_message` VALUES (12, 4, 3, 0, '', 1, 1, 1, 1588755315);
INSERT INTO `system_message` VALUES (13, 4, 5, 5, '', 0, 1, 1, 1588755644);
INSERT INTO `system_message` VALUES (14, 3, 5, 5, '', 0, 1, 1, 1588755646);
INSERT INTO `system_message` VALUES (15, 2, 5, 5, '', 0, 0, 0, 1588755648);
INSERT INTO `system_message` VALUES (16, 1, 5, 5, '', 0, 1, 1, 1588755650);
INSERT INTO `system_message` VALUES (17, 5, 4, 0, '', 1, 1, 1, 1588755679);
INSERT INTO `system_message` VALUES (18, 5, 3, 0, '', 1, 1, 1, 1588756886);
INSERT INTO `system_message` VALUES (19, 5, 1, 0, '', 1, 1, 1, 1588757223);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '密码',
  `nickname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `sign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '签名',
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '状态：online在线 hide隐身 offline离线',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'test1', '$2y$10$.RLa3H.GHcK90tXKW4owTO2z6oqHheqfx/tWPuxwdjUYs7cRMUvgG', '策适伊', '/Static/upload/5eb247df9703fxixi.jpg', '测试的目的是为了尽可能多地找出错误', 'offline');
INSERT INTO `user` VALUES (2, 'test2', '$2y$10$NFrbCABP0AhtD3vjpoRxjO7wYa6JqquTQsSVT7zNxE8TBkoQgB4/y', '策适尔', '/Static/upload/5eb24c84af706ceshier.jpg', 'Bug逃不过偶的法眼', 'offline');
INSERT INTO `user` VALUES (3, 'tianming', '$2y$10$VMhJPQ0wmhErivitV26aiuj/bCLTf9/n5FyZbFKt1h3XEtg4DmHFi', '天明', '/Static/upload/5eb24d42d9244tianming.png', '墨家巨子', 'online');
INSERT INTO `user` VALUES (4, 'xiaobao', '$2y$10$Bx3VpIzxT7n.3Z.vBN1EWeWOz1jCvu1OFGG9VAOKMHbZjnYhNhYsi', '小宝', '/Static/upload/5eb27ad9d77bd小女孩.jpg', '宝宝乐意', 'offline');
INSERT INTO `user` VALUES (5, 'wukong', '$2y$10$n.Qc98l/egNJOOyhpke2OOWg3xbshwyLsMHgACsNPAE8vxx4KMAEK', '悟空', '/Static/upload/5eb27c4b00a48sun.jpg', '我要七龙珠召唤神龙', 'online');

SET FOREIGN_KEY_CHECKS = 1;
