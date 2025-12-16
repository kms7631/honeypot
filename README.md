# 허니팟 관리자 로그인 데모

## 프로젝트 구조

```
public/
  index.php            # 테스트용 메인
  admin.php            # 허니팟 로그인/처리
  dashboard_login.php  # 관리자 로그인
  dashboard.php        # 대시보드(로그/차단/버튼)
  logout.php           # 로그아웃
  banned.html          # 차단 안내
assets/
  style.css            # 최소 스타일
  app.js               # 필요 최소만
app/
  config.php           # DB/관리자 설정
  db.php               # PDO 연결
  check_ban.php        # 차단 확인
  helpers.php          # 마스킹/이스케이프
sql/
  schema.sql           # 테이블 생성 쿼리
README.md              # 실행/테스트 안내
```

## 설치 및 실행 방법

1. MySQL 8.0에서 `sql/schema.sql` 실행

2. `app/config.php`에서 DB 정보(DB_HOST, DB_NAME, DB_USER, DB_PASS)와 관리자 계정(ADMIN_ID, ADMIN_PW) 설정

3. Apache 2.4 + PHP 7.4 환경에서 public/을 DocumentRoot로 지정

4. 브라우저에서 `/index.php` 접속

## 테스트 시나리오

1. `/admin.php`에서 임의 ID/PW로 3회 이상 로그인 시도 → 차단 동작 확인(3회 시도 후 /banned.html로 이동)
2. `/dashboard_login.php`에서 관리자 로그인 → `/dashboard.php`에서 로그/차단 목록 확인
3. 대시보드에서 "즉시 차단"/"차단 해제" 버튼 동작 확인
4. "원문 보기" 버튼 클릭 시 마스킹이 아닌 원문 PW 확인(관리자만)

## 기타
- DB는 PDO + Prepared Statement만 사용
- 모든 출력은 HTML 이스케이프 처리
- 프론트엔드는 HTML5/CSS3/JS만, 디자인은 단순
- 데모용이므로 비밀번호 원문 저장 허용(기본은 마스킹)
