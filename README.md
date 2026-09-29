# SUN LAB Tools — 部署说明

独立 WordPress 插件，为 TinkerLog 提供**纯浏览器端**的免费在线工具。
不影响主题、不修改核心文件、不依赖任何第三方工具插件。

**当前版本：0.3.0 — 11 个工具全部上线。**

---

## 一、这个插件做什么

- 注册工具（当前 **11 个，全部 live**）
- 每个工具 = 一个独立 URL 的 WordPress 页面（SEO 由 Rank Math 接管）
- **只在该工具所在的页面加载该工具的 CSS/JS** —— 其他页面零污染
- 渲染 `How to use` + `FAQ`（自动输出 FAQPage 结构化数据）+ `Related tools` 内链
- 工具逻辑**全部在浏览器本地执行**，不上传任何数据

---

## 二、文件结构

```
sun-lab-tools/
├── sun-lab-tools.php               # 主入口
├── includes/
│   ├── class-slt-registry.php      # 工具注册表（单一数据源）
│   ├── class-slt-assets.php        # 条件加载 CSS/JS
│   ├── class-slt-shortcode.php     # [sun_tool] / [sun_tool_index]
│   ├── class-slt-seo.php           # Rank Math SEO 自动写入
│   └── class-slt-tool-page.php     # 自动建页 + 后台管理页
├── assets/
│   ├── css/base.css                # 容器基础样式
│   ├── css/components.css          # 全工具共用组件（按钮/表格/树/指标…）
│   └── js/base.js                  # window.SLT 公共工具函数
└── tools/
    ├── json-formatter/             # 每个工具固定 4 个文件
    │   ├── tool.php                # UI 结构
    │   ├── tool.js                 # 纯前端逻辑
    │   ├── tool.css                # 作用域样式（.slt- 前缀）
    │   └── content.php             # How-to + FAQ 文案
    ├── json-validator/
    ├── json-minifier/
    ├── json-viewer/
    ├── jwt-decoder/
    ├── base64-encoder/
    ├── base64-decoder/
    ├── uuid-generator/
    ├── timestamp-converter/
    ├── url-encoder/
    └── url-decoder/
```

---

## 三、安装方式（二选一）

### 方式 A：后台上传 zip（推荐）

1. 进入 `wp-admin` → **插件 → 安装插件 → 上传插件**
2. 选择 `sun-lab-tools.zip`
3. 安装 → 启用

### 方式 B：手动上传目录

1. 把整个 `sun-lab-tools/` 目录上传到 `wp-content/plugins/`
2. 进入 **插件** 页面启用 **SUN LAB Tools**

---

## 三·GitHub 自动更新（自 v0.3.0 起）

插件内置 [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)（已打包在 `plugin-update-checker/`），
指向公开仓库 **`sunfei0318-code/sun-lab-tools`** 的 `main` 分支。

**以后发新版只需两步，不用再手动传插件：**

1. 改代码，把 `sun-lab-tools.php` 顶部的 `Version: 0.3.0` 改成新版本号（如 `0.3.1`）；
2. 把改动推到 GitHub 并打一个 **`v` 开头的 tag**（如 `v0.3.1`）：

   ```bash
   git add -A
   git commit -m "release: v0.3.1"
   git tag v0.3.1
   git push origin main --tags
   ```

WordPress 后台「插件」页会在几分钟内检测到新版本并提供「更新」。
（插件每 12 小时向 GitHub 检查一次，也可在后台点「重新检查更新」立即触发。）

> 仓库需**公开**。如果用私有仓库，取消 `sun-lab-tools.php` 末尾
> `setAuthentication('ghp_...')` 那行注释并填入有 `read:packages` 权限的 Token。

---

## 四、启用后会自动做什么

启用瞬间（activation hook）自动执行：

1. 创建 **`/tools/`** 目录页（内容 = `[sun_tool_index]`）
2. 为每个 `status: live` 的工具创建页面，例如 **`/json-formatter/`**
3. 刷新 rewrite 规则

> **已有的页面绝不会被覆盖** —— 若 slug 已存在，插件直接复用。

---

## 五、手动重跑建页 / 同步 SEO

**方式 1（后台）**：`工具 → SUN LAB Tools`
- 点 **"Create / update tool pages"** → 补齐缺失的工具页面
- 点 **"Sync tool page SEO"** → 把注册表里的 SEO 文案写入 Rank Math

**方式 2（WP-CLI）**：
```bash
wp eval 'SLT_Tool_Page::instance()->ensure_pages();'
wp eval 'SLT_Seo::instance()->sync_tool_pages();'
```

该后台页还会列出所有已注册工具、状态、页面链接和 SEO 写入情况，方便核对。

---

## 六、短代码

| 短代码 | 作用 |
|---|---|
| `[sun_tool slug="json-formatter"]` | 渲染单个工具（含 FAQ / 相关工具） |
| `[sun_tool_index]` | 渲染完整工具目录（按分类分组） |

可选属性：
```
[sun_tool slug="json-formatter" related="yes" faq="yes"]
```

---

## 七、如何新增一个工具

**两步。**

### 第 1 步：建目录

```
tools/my-tool/
├── tool.php      # UI 结构
├── tool.js       # 前端逻辑
├── tool.css      # 样式（.slt- 前缀）
└── content.php   # howto + faq 文案
```

`content.php` 固定返回数组：
```php
return array(
    'howto' => array( '第一步', '第二步' ),
    'faq'   => array(
        array( '问题？', '回答。' ),
    ),
);
```

### 第 2 步：注册一条

在 `includes/class-slt-registry.php` 的 `default_tools()` 里加：

```php
array(
    'slug'             => 'my-tool',
    'name'             => 'My Tool',
    'category'         => 'Developer Tools',
    'status'           => 'live',          // live | soon
    'short'            => '一句话描述。',
    'related'          => array( 'json-formatter' ),
    // SEO（可选但建议填，插件会自动写入 Rank Math）
    'seo_title'        => 'My Tool — Free Online ...',
    'seo_description'  => '一句话 SEO 描述，控制在 155 字符内。',
    'focus_keyword'    => 'my tool',
),
```

然后到 `工具 → SUN LAB Tools` 点一次 **"Create / update tool pages"**，
再点 **"Sync tool page SEO"** 即可。

> **约定**：`tool.php` 里的根容器 class 必须包含 `.slt-{slug}`，
> 因为 `tool.js` 靠它定位根节点（见下方）。

---

## 八、Rank Math SEO 自动写入

插件内置 `class-slt-seo.php`，会把注册表里的 `seo_title` /
`seo_description` / `focus_keyword` **直接写入 Rank Math 的 post meta**
（`rank_math_title` / `rank_math_description` / `rank_math_focus_keyword`）。

**为什么直接写 meta 而不是走 Rank Math 的界面**：Rank Math 的 SEO 面板是
React 动态渲染的，其 REST 路由 `rankmath/v1/updateMeta` 绑定 nonce；
而 Rank Math 最终读取的仍然是 `get_post_meta()`。直接写 meta 是可靠路径。

**保护机制**：`sync_tool_pages()` 默认**只在值为空时写入**，
你在后台手动改过的 SEO 不会被覆盖。需要强制覆盖时，在后台勾选
"Overwrite existing values" 再点同步。

---

## 九、前端 JS 约定

所有工具共用 `assets/js/base.js` 提供的 `window.SLT` 助手，工具文件本身
只写自己的业务逻辑：

| 助手 | 作用 |
|---|---|
| `SLT.text(cfg, key, fallback)` | 取 `wp_localize_script` 注入的翻译串 |
| `SLT.copy(value, done, fail)` | 复制到剪贴板（含 textarea 兜底） |
| `SLT.flash(btn, msg, ms)` | 按钮临时改字提示已复制 |
| `SLT.bytes(str)` / `SLT.humanBytes(n)` | UTF-8 字节数 / 人类可读体积 |
| `SLT.error(box, msg)` | 显示或隐藏错误框 |
| `SLT.pill(el, msg, tone)` | 设置状态标签（ok / bad / neutral） |
| `SLT.boot(rootClass, init)` | DOM ready 后按根 class 挂载 |

`tool.js` 的标准写法：

```js
( function () {
    'use strict';
    var T = window.SLT.text;

    window.SLT.boot( 'slt-my-tool', function ( root ) {
        var cfg   = window.SLT_my_tool || {};
        var input = root.querySelector( '[data-slt-input]' );
        // ... 业务逻辑
    } );
} )();
```

插件会自动把 `SLT_{slug}` 配置对象注入到页面（含 `root` 和 `i18n`）。

**样式约定**：按钮、输入框、错误框、表格、树、指标卡等**一律使用
`components.css` 里的 `.slt-*` 类**，`tool.css` 只放该工具独有的规则。

---

## 十、SEO 说明

- 工具页是**普通 WordPress 页面** → Rank Math 正常输出 title / description / canonical / OG
- 插件额外输出 **FAQPage** JSON-LD（来自 `content.php`）
- `/tools/` 目录页与所有工具页都在 `page-sitemap.xml` 中
- 每个工具的 `seo_title` ≤ 60 字符、`seo_description` ≤ 160 字符，避免 SERP 截断

---

## 十一、缓存注意

插件用 `filemtime()` 做资源版本号，改完 `tool.css` / `tool.js` 后**无需手动清缓存**。
但若站点启用了 WP Super Cache，修改**页面内容**后仍需清一次页面缓存。

---

## 十二、已验证

- ✅ 28 个 PHP 文件全部 `php -l` 通过
- ✅ 12 个 JS 文件全部 `node --check` 通过
- ✅ 注册表校验：11 个工具字段齐全 / 文件齐全 / related 链接可解析 —— 0 问题
- ✅ 工具算法测试 **190 条断言全部通过**，其中包含：
  - JSON 格式化 4 种缩进、格式↔压缩往返、5 类非法输入正确报错
  - Base64 全 UTF-8 往返（emoji / 中文 / 5000 字符大串）、URL-safe 与 MIME 折行、二进制回退 hex
  - URL 编码四种模式，并验证了「未转义的 `&` 会拆散参数」这一工具存在的原因
  - JWT 三段落解码、`Bearer ` 前缀剥离、`alg:none` 识别、exp/iat/nbf 时间换算
  - UUID v4：**10 万个零碰撞**，版本位/变体位全部合法
  - UUID v7：**2000 个连续毫秒严格递增**，内嵌时间戳可反解
  - 时间戳：秒/毫秒自动判别、Y2038 边界、多种日期格式解析

---

## 十三、当前工具清单（11 个，全部 live）

| 工具 | Slug | 页面 |
|---|---|---|
| JSON Formatter | `json-formatter` | `/json-formatter/` |
| JSON Validator | `json-validator` | `/json-validator/` |
| JSON Minifier | `json-minifier` | `/json-minifier/` |
| JSON Viewer | `json-viewer` | `/json-viewer/` |
| JWT Decoder | `jwt-decoder` | `/jwt-decoder/` |
| Base64 Encoder | `base64-encoder` | `/base64-encoder/` |
| Base64 Decoder | `base64-decoder` | `/base64-decoder/` |
| UUID Generator | `uuid-generator` | `/uuid-generator/` |
| Unix Timestamp Converter | `timestamp-converter` | `/timestamp-converter/` |
| URL Encoder | `url-encoder` | `/url-encoder/` |
| URL Decoder | `url-decoder` | `/url-decoder/` |

工具目录页：**`/tools/`**
