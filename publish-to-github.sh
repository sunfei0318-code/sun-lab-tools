#!/usr/bin/env bash
#
# 一键把 SUN LAB Tools 推送到 GitHub 并打 tag v0.3.0。
# 前置：本机已安装 gh CLI 并登录（`gh auth login`）。
#
# 用法：
#   chmod +x publish-to-github.sh
#   ./publish-to-github.sh
#
set -euo pipefail

REPO="sunfei0318-code/sun-lab-tools"
TAG="v0.3.0"
BRANCH="main"

cd "$(dirname "$0")"

# 若解压后没有 .git（zip 不含历史），则初始化并提交当前内容
if [ ! -d .git ]; then
  git init -q
  git config user.email "${GIT_EMAIL:-sunlab@local}"
  git config user.name "${GIT_NAME:-SUN LAB}"
  git add -A
  git commit -q -m "chore: ${TAG} — add GitHub auto-update (Puc v5.4) and README"
  git branch -M "$BRANCH"
  git tag "$TAG"
fi

# 创建公开仓库（若不存在）并推送；否则直接推
if gh repo view "$REPO" >/dev/null 2>&1; then
  git remote add origin "https://github.com/${REPO}.git" 2>/dev/null || true
  git push -u origin "$BRANCH" --tags
else
  gh repo create "$REPO" --public --source=. --push
  git tag "$TAG" 2>/dev/null || true
  git push origin "$BRANCH" --tags
fi

echo ""
echo "✅ 完成。仓库地址： https://github.com/${REPO}"
echo "   WordPress 插件将在几分钟内从 main 分支 + tag 检测更新。"
