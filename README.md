# honeypot
현장 실습 기간 제작한 프로젝트 (허니팟 관리자 로그인 데모)

## 프로젝트 구조

```
public/
  index.php            # 데모 메인(랜딩)
  admin.php            # 허니팟 로그인/처리(공격 로그 기록)
  dashboard_login.php  # 관리자 로그인
  dashboard.php        # 대시보드(로그/수동차단/삭제/시험데이터)
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

## 동작 방식(중요)

- `/admin.php`는 **로그인 시도(공격 로그)만 기록**합니다. 자동 차단은 하지 않습니다.
- 위험 기준은 `HONEYPOT_BAN_THRESHOLD`(설정값)이며, 대시보드에서 **위험 표시** 후 관리자가 **수동으로 차단**합니다.

## 테스트 시나리오

1. `/admin.php`에서 임의 ID/PW로 여러 번 로그인 시도 → `attack_log`에 기록이 쌓이는지 확인
2. `/dashboard_login.php`에서 관리자 로그인 → `/dashboard.php`에서 IP별 목록/시도횟수 확인
3. IP 행의 ▶/▼ 토글을 눌러, 해당 IP가 시도한 계정 목록(로그 상세) 펼치기/접기 확인
4. 위험 표시된 IP의 "차단" 버튼으로 수동 차단 동작 확인
5. 상단 "기록 삭제" 버튼을 눌러 삭제 모드 전환 → 개별 로그 "삭제" 동작 확인
6. "원문 보기" 버튼을 눌러 비밀번호 원문/마스킹 토글 확인
7. 차단 IP 목록에서 "차단 IP 시험 데이터 추가" 클릭 → 3개 시험 IP 생성 확인
8. 차단 IP 목록에서도 ▶/▼ 토글로 랜덤 계정 리스트가 표시되는지 확인(데모용)

## 기타
- DB는 PDO + Prepared Statement만 사용
- 모든 출력은 HTML 이스케이프 처리
- 프론트엔드는 HTML5/CSS3/JS만 사용
- 데모용이므로 비밀번호 원문 저장 허용(기본은 마스킹)
