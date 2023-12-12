test:
	./bpc-prepare.sh src.list
	$(MAKE) -C ./bpc-test test-bin
	mv ./bpc-test/test .

test-bin:
	bpc -v \
	    -c ../../src/bpc.conf \
	    -o  test\
	    -u phpunit \
	    -u react-promise \
	    -d display_errors=on \
	    -d suppress_runtime_too_many_arguments_warning=1 \
	    run-test.php \
	    --input-file src.list
