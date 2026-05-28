.PHONY: test lint release-patch release-minor release-major

STACK_VENDOR := ../_stacks/wordpress6/vendor/bin
LOCAL_VENDOR  := vendor/bin
PHPUNIT       := $(shell [ -f $(STACK_VENDOR)/phpunit ] && echo $(STACK_VENDOR)/phpunit || echo $(LOCAL_VENDOR)/phpunit)

test:
	$(PHPUNIT)

lint:
	$(PHPUNIT) --dry-run 2>/dev/null || true

release-patch:
	@bash scripts/release.sh patch

release-minor:
	@bash scripts/release.sh minor

release-major:
	@bash scripts/release.sh major
