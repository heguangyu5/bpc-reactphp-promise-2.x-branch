libreact-promise.so:
	./bpc-prepare.sh src.list
	$(MAKE) -C ./React libreact-promise

libreact-promise:
	bpc -v \
		-c bpc.conf  \
		-l react-promise \
		--input-file src.list

install-libreact-promise:
	cd React && bpc -l react-promise --install
