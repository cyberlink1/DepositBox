#!/usr/bin/env python3
import base64
import glob
import os
import random
import string

try:
    import nacl.public
except ImportError:
    print('PyNaCl is required: "pip install pynacl" or similar')
    exit(1)


def key_str(key):
    # bytes to base 32
    key_bytes = bytes(key)
    key_b32 = base64.b32encode(key_bytes)
    # strip trailing ====
    assert key_b32[-4:] == b'===='
    key_b32 = key_b32[:-4]
    # change from b'ASDF' to ASDF
    s = key_b32.decode('utf-8')
    return s


def main():
    os.system('clear')
    directory = {}
    file = {}
    service = {}
    count = 0
    print(" Select the hidden service to creat a client key for ")
    print("-----------------------------------------------------")
    for f in glob.glob('/var/lib/tor/**/hostname', recursive=True):
        head_tail = os.path.split(f)
        directory[count] = head_tail[0]
        file[count] = head_tail[1]
        service[count] = head_tail[0].split('/', 4)[-1]
        print(str(count) + ") " + service[count])
        count += 1
    print("\n\n")
    txt = input("Select the hidden service to use: ")
    try:
        if int(txt) <= count-1 :
            name = "".join(random.choice(string.ascii_lowercase) for i in range(10))
            print("You selected: " + service[int(txt)])
            f = open(directory[int(txt)] + "/" + file[int(txt)], "r")
            hostname = f.read().split(".")
            print("Hostname is: " + hostname[0] + "." + hostname[1])
            f.close()
            print("Generating Key")
            priv_key = nacl.public.PrivateKey.generate()
            pub_key = priv_key.public_key
            print("Writing auth key to tor hidden service")
            f = open(directory[int(txt)] + "/authorized_clients/" + name + ".auth", "w")
            g = open(name + ".auth_private", "w")
            f.write("descriptor:x25519:" + key_str(pub_key))
            f.close()
            os.chmod(directory[int(txt)] + "/authorized_clients/" + name + ".auth", 0o644)
            stat_info = os.stat(directory[int(txt)])
            uid = stat_info.st_uid
            gid = stat_info.st_gid
            os.chown(directory[int(txt)] + "/authorized_clients/" + name + ".auth", uid, gid)
            print("Key name: " + name + ".auth .... done")
            print("Writing private key to local directory")
            g.write(hostname[0] + ":descriptor:x25519:" + key_str(priv_key))
            g.close
            print("Key name: " + name + ".auth_private .... done")
        else:
            print("No hidden service found")
    except ValueError:
            print("Invalid Selection")


if __name__ == '__main__':
    exit(main())
