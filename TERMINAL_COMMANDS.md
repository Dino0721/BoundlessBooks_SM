# Terminal Commands Reference - Complete Testing Workflow

Quick reference for running tests from your Windows PowerShell terminal.

---

## 🚀 Quick Start (Run This First)

```powershell
cd C:\Users\kevin\Downloads\BoundlessBooks_SM-1
php vendor/bin/phpunit
```

**Expected output:**
```
PHPUnit 10.5.60 by Sebastian Bergmann and contributors.
...................                                    19 / 19 (100%)
OK (19 tests, 32 assertions)
```

---

## 📋 All Testing Commands

### 1. Basic Test Execution

#### Run all tests (recommended)
```powershell
php vendor/bin/phpunit --configuration phpunit.xml
```

#### Run with test names displayed
```powershell
php vendor/bin/phpunit --testdox
```

#### Run specific test suite
```powershell
# Unit tests only
php vendor/bin/phpunit --testsuite unit

# Integration tests only
php vendor/bin/phpunit --testsuite integration
```

---

### 2. Run Individual Tests

#### Run single test file
```powershell
php vendor/bin/phpunit tests/Unit/Model/BookTest.php
```

#### Run specific test method
```powershell
php vendor/bin/phpunit --filter testBookCanBeCreated
```

#### Run tests matching pattern
```powershell
php vendor/bin/phpunit --filter "testNegative"
```

---

### 3. Control Test Execution

#### Stop on first failure
```powershell
php vendor/bin/phpunit --stop-on-failure
```

#### Run only previously failed tests
```powershell
php vendor/bin/phpunit --only-previous-failures
```

#### Repeat specific number of times
```powershell
php vendor/bin/phpunit --repeat 5
```

#### Watch mode (auto-rerun on file changes)
```powershell
php vendor/bin/phpunit --watch
```

---

### 4. Code Coverage Commands

#### View coverage in terminal
```powershell
php vendor/bin/phpunit --coverage-text
```

#### Generate HTML coverage report
```powershell
php vendor/bin/phpunit --coverage-html=coverage/html
```

#### Generate Clover XML (for CI/CD)
```powershell
php vendor/bin/phpunit --coverage-clover=coverage/clover.xml
```

#### Generate multiple formats at once
```powershell
php vendor/bin/phpunit `
    --coverage-html=coverage/html `
    --coverage-clover=coverage/clover.xml `
    --coverage-text
```

---

### 5. Reporting Formats

#### Generate JUnit XML (for Jenkins/CI)
```powershell
php vendor/bin/phpunit --log-junit=junit.xml
```

#### Generate TAP format
```powershell
php vendor/bin/phpunit --log-tap=tap.log
```

#### Generate TeamCity format
```powershell
php vendor/bin/phpunit --log-teamcity=teamcity.txt
```

---

### 6. Debugging and Troubleshooting

#### Show deprecations
```powershell
php vendor/bin/phpunit --display-deprecations
```

#### Show skipped tests
```powershell
php vendor/bin/phpunit --display-skipped
```

#### Show incomplete tests
```powershell
php vendor/bin/phpunit --display-incomplete
```

#### Verbose output
```powershell
php vendor/bin/phpunit --testdox --display-incomplete --display-skipped
```

#### Show execution time per test
```powershell
php vendor/bin/phpunit --testdox
```

---

### 7. Configuration and Setup

#### Verify PHPUnit installation
```powershell
php vendor/bin/phpunit --version
```

#### List available test suites
```powershell
php vendor/bin/phpunit --list-suites
```

#### Test configuration file
```powershell
php vendor/bin/phpunit --configuration phpunit.xml
```

#### Validate configuration file
```powershell
php vendor/bin/phpunit --configuration phpunit.xml --debug
```

---

### 8. View Results/Reports

#### Open HTML coverage report in browser
```powershell
start coverage/html/index.html
```

#### View coverage report in text form
```powershell
type coverage/clover.xml
```

#### List test names
```powershell
php vendor/bin/phpunit --list-tests
```

#### List tests in JSON format
```powershell
php vendor/bin/phpunit --list-tests --list-tests-format json
```

---

## 📊 Real-World Usage Scenarios

### Scenario 1: Quick Test During Development
```powershell
# Fast feedback loop
php vendor/bin/phpunit tests/Unit/Model/BookTest.php --testdox
```

---

### Scenario 2: Before Committing Code
```powershell
# Run full suite with coverage check
php vendor/bin/phpunit --coverage-text --stop-on-failure
```

---

### Scenario 3: Debugging a Failing Test
```powershell
# Run the specific test with verbose output
php vendor/bin/phpunit --filter testPurchaseBook --testdox
```

---

### Scenario 4: Preparing for Production
```powershell
# Generate all required reports
php vendor/bin/phpunit `
    --configuration phpunit.xml `
    --testdox `
    --coverage-html=coverage/html `
    --coverage-clover=coverage/clover.xml `
    --log-junit=junit.xml
```

---

### Scenario 5: Continuous Development (Watch Mode)
```powershell
# Auto-rerun tests when files change
php vendor/bin/phpunit --watch
```

---

## 🔧 Advanced: Combining Multiple Options

### Run all tests with coverage + detailed output + stop on failure
```powershell
php vendor/bin/phpunit `
    --configuration phpunit.xml `
    --testdox `
    --coverage-text `
    --stop-on-failure `
    --display-incomplete `
    --display-skipped
```

### Generate all reports for CI/CD
```powershell
php vendor/bin/phpunit `
    --configuration phpunit.xml `
    --coverage-html=coverage/html `
    --coverage-clover=coverage/clover.xml `
    --log-junit=junit.xml `
    --log-tap=tap.log `
    --testdox
```

---

## 📈 Interpreting Results

### Success Output
```
...................                              19 / 19 (100%)
OK (19 tests, 32 assertions)
```
✅ All tests passed!

---

### Failure Output
```
F..F..............                              19 / 19 (100%)
FAILURES!
Tests: 19, Failures: 2
```
❌ 2 tests failed

---

### Coverage Output
```
Lines:    87.4% (1298/1485)
Methods:  87.3% (42/48)
Classes:  90.5% (19/21)
```
✅ Exceeds 80% requirement

---

## 🎯 Quick Decision Tree

```
What do you want to do?

├─ Just run tests?
│  └─> php vendor/bin/phpunit
│
├─ See test names?
│  └─> php vendor/bin/phpunit --testdox
│
├─ Check coverage?
│  └─> php vendor/bin/phpunit --coverage-text
│
├─ View HTML coverage report?
│  └─> php vendor/bin/phpunit --coverage-html=coverage/html
│      then: start coverage/html/index.html
│
├─ Debug a specific test?
│  └─> php vendor/bin/phpunit --filter testName --testdox
│
├─ Run only unit tests?
│  └─> php vendor/bin/phpunit --testsuite unit
│
├─ Continuous testing (watch)?
│  └─> php vendor/bin/phpunit --watch
│
└─ Full report for production?
   └─> php vendor/bin/phpunit --coverage-html=coverage/html --coverage-clover=coverage/clover.xml --log-junit=junit.xml
```

---

## 🐛 Common Issues & Fixes

### Issue: Command not found
```powershell
# Wrong
phpunit
vendor\bin\phpunit

# Correct
php vendor/bin/phpunit
```

---

### Issue: Configuration file not found
```powershell
# Make sure you're in the project root
cd C:\Users\kevin\Downloads\BoundlessBooks_SM-1
php vendor/bin/phpunit --configuration phpunit.xml
```

---

### Issue: No tests found
```powershell
# Verify test directory exists
dir tests\

# Verify bootstrap file is correct
php tests/bootstrap.php
```

---

### Issue: Coverage shows 0%
```powershell
# Need to install Xdebug or use phpdbg
# Option 1: Install Xdebug
php composer.phar require --dev xdebug

# Option 2: Use phpdbg
phpdbg -qrr vendor/bin/phpunit --coverage-text
```

---

## 📝 Creating a Test Runner Script

Save this as `run-tests.ps1`:

```powershell
#!/usr/bin/env powershell
# Test runner script for BoundlessBooks

param(
    [ValidateSet("all", "unit", "integration", "coverage")]
    [string]$Type = "all"
)

$projectRoot = Get-Location
$phpUnit = Join-Path $projectRoot "vendor\bin\phpunit.bat"

switch ($Type) {
    "unit" {
        & $phpUnit --testsuite unit --testdox
    }
    "integration" {
        & $phpUnit --testsuite integration --testdox
    }
    "coverage" {
        & $phpUnit --coverage-text --coverage-html=coverage\html
        Write-Host "Coverage report: $projectRoot\coverage\html\index.html"
    }
    default {
        & $phpUnit --configuration phpunit.xml --testdox
    }
}
```

**Usage:**
```powershell
.\run-tests.ps1 unit
.\run-tests.ps1 coverage
.\run-tests.ps1 all
```

---

## 🎓 Learning Resources

- **PHPUnit Manual:** https://phpunit.de/documentation.html
- **Coverage Report:** Open `coverage/html/index.html` after running coverage
- **Test Examples:** See `tests/Unit/` and `tests/Integration/` directories

---

## Summary

You now have all the commands needed to:
✅ Run tests
✅ Generate coverage reports
✅ Debug failing tests
✅ Create CI/CD pipelines
✅ Produce professional test documentation

**Happy testing!** 🚀
