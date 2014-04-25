<?php
/**
 * Copyright (c) 2014 Aurélien RICHAUD
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Created 17/04/14 14:56 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol\Component;


use PhpCoinD\Exception\CScriptNotValid;
use PhpCoinD\Protocol\BigNum;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;

class CScript {
    // Push values
    const OP_0 = 0x00;
    const OP_PUSHDATA1 = 0x4c;
    const OP_PUSHDATA2 = 0x4d;
    const OP_PUSHDATA4 = 0x4e;
    const OP_1NEGATE = 0x4f;
    const OP_RESERVED = 0x50;
    const OP_1 = 0x51;
    const OP_2 = 0x52;
    const OP_3 = 0x53;
    const OP_4 = 0x54;
    const OP_5 = 0x55;
    const OP_6 = 0x56;
    const OP_7 = 0x57;
    const OP_8 = 0x58;
    const OP_9 = 0x59;
    const OP_10 = 0x5a;
    const OP_11 = 0x5b;
    const OP_12 = 0x5c;
    const OP_13 = 0x5d;
    const OP_14 = 0x5e;
    const OP_15 = 0x5f;
    const OP_16 = 0x60;

    const OP_FALSE = self::OP_0;
    const OP_TRUE = self::OP_1;

    // Control
    const OP_NOP = 0x61;
    const OP_VER = 0x62;
    const OP_IF = 0x63;
    const OP_NOTIF = 0x64;
    const OP_VERIF = 0x65;
    const OP_VERNOTIF = 0x66;
    const OP_ELSE = 0x67;
    const OP_ENDIF = 0x68;
    const OP_VERIFY = 0x69;
    const OP_RETURN = 0x6a;

    // Stack operations
    const OP_TOALTSTACK = 0x6b;
    const OP_FROMALTSTACK = 0x6c;
    const OP_2DROP = 0x6d;
    const OP_2DUP = 0x6e;
    const OP_3DUP = 0x6f;
    const OP_2OVER = 0x70;
    const OP_2ROT = 0x71;
    const OP_2SWAP = 0x72;
    const OP_IFDUP = 0x73;
    const OP_DEPTH = 0x74;
    const OP_DROP = 0x75;
    const OP_DUP = 0x76;
    const OP_NIP = 0x77;
    const OP_OVER = 0x78;
    const OP_PICK = 0x79;
    const OP_ROLL = 0x7a;
    const OP_ROT = 0x7b;
    const OP_SWAP = 0x7c;
    const OP_TUCK = 0x7d;

    // Splice ops
    const OP_CAT = 0x7e;
    const OP_SUBSTR = 0x7f;
    const OP_LEFT = 0x80;
    const OP_RIGHT = 0x81;
    const OP_SIZE = 0x82;

    // Bit logic
    const OP_INVERT = 0x83;
    const OP_AND = 0x84;
    const OP_OR = 0x85;
    const OP_XOR = 0x86;
    const OP_EQUAL = 0x87;
    const OP_EQUALVERIFY = 0x88;
    const OP_RESERVED1 = 0x89;
    const OP_RESERVED2 = 0x8a;

    // Numeric
    const OP_1ADD = 0x8b;
    const OP_1SUB = 0x8c;
    const OP_2MUL = 0x8d;
    const OP_2DIV = 0x8e;
    const OP_NEGATE = 0x8f;
    const OP_ABS = 0x90;
    const OP_NOT = 0x91;
    const OP_0NOTEQUAL = 0x92;
    const OP_ADD = 0x93;
    const OP_SUB = 0x94;
    const OP_MUL = 0x95;
    const OP_DIV = 0x96;
    const OP_MOD = 0x97;
    const OP_LSHIFT = 0x98;
    const OP_RSHIFT = 0x99;

    const OP_BOOLAND = 0x9a;
    const OP_BOOLOR = 0x9b;
    const OP_NUMEQUAL = 0x9c;
    const OP_NUMEQUALVERIFY = 0x9d;
    const OP_NUMNOTEQUAL = 0x9e;
    const OP_LESSTHAN = 0x9f;
    const OP_GREATERTHAN = 0xa0;
    const OP_LESSTHANOREQUAL = 0xa1;
    const OP_GREATERTHANOREQUAL = 0xa2;
    const OP_MIN = 0xa3;
    const OP_MAX = 0xa4;

    const OP_WITHIN = 0xa5;

    // Crypto
    const OP_RIPEMD160 = 0xa6;
    const OP_SHA1 = 0xa7;
    const OP_SHA256 = 0xa8;
    const OP_HASH160 = 0xa9;
    const OP_HASH256 = 0xaa;
    const OP_CODESEPARATOR = 0xab;
    const OP_CHECKSIG = 0xac;
    const OP_CHECKSIGVERIFY = 0xad;
    const OP_CHECKMULTISIG = 0xae;
    const OP_CHECKMULTISIGVERIFY = 0xaf;

    // Expansion
    const OP_NOP1 = 0xb0;
    const OP_NOP2 = 0xb1;
    const OP_NOP3 = 0xb2;
    const OP_NOP4 = 0xb3;
    const OP_NOP5 = 0xb4;
    const OP_NOP6 = 0xb5;
    const OP_NOP7 = 0xb6;
    const OP_NOP8 = 0xb7;
    const OP_NOP9 = 0xb8;
    const OP_NOP10 = 0xb9;

    // Template matching params
    const OP_SMALLINTEGER = 0xfa;
    const OP_PUBKEYS = 0xfb;
    const OP_PUBKEYHASH = 0xfd;
    const OP_PUBKEY = 0xfe;

    const OP_INVALIDOPCODE = 0xff;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $raw_data;

    protected $opcodes_functions = array(
        self::OP_0 => 'op_0',
        self::OP_PUSHDATA1 => 'op_pushdata1',
        self::OP_PUSHDATA2 => 'op_pushdata2',
        self::OP_PUSHDATA4 => 'op_pushdata4',
        self::OP_1NEGATE => 'op_1negate',
        self::OP_1 => 'op_1',
        self::OP_2 => 'op_2',
        self::OP_3 => 'op_3',
        self::OP_4 => 'op_4',
        self::OP_5 => 'op_5',
        self::OP_6 => 'op_6',
        self::OP_7 => 'op_7',
        self::OP_8 => 'op_8',
        self::OP_9 => 'op_9',
        self::OP_10 => 'op_10',
        self::OP_11 => 'op_11',
        self::OP_12 => 'op_12',
        self::OP_13 => 'op_13',
        self::OP_14 => 'op_14',
        self::OP_15 => 'op_15',
        self::OP_16 => 'op_16',

        self::OP_NOP => 'op_nop',
        self::OP_VER => 'op_ver',
        self::OP_IF => 'op_if',
        self::OP_NOTIF => 'op_notif',
        self::OP_VERIF => 'op_verif',
        self::OP_VERNOTIF => 'op_vernotif',
        self::OP_ELSE => 'op_else',
        self::OP_ENDIF => 'op_endif',
        self::OP_VERIFY => 'op_verify',
        self::OP_RETURN => 'op_return',

        self::OP_TOALTSTACK => 'op_toaltstack',
        self::OP_FROMALTSTACK => 'op_fromaltstack',
        self::OP_2DROP => 'op_2drop',
        self::OP_2DUP => 'op_2dup',
        self::OP_3DUP => 'op_3dup',
        self::OP_2OVER => 'op_2over',
        self::OP_2ROT => 'op_2rot',
        self::OP_2SWAP => 'op_2swap',
        self::OP_IFDUP => 'op_ifdup',
        self::OP_DEPTH => 'op_depth',
        self::OP_DROP => 'op_drop',
        self::OP_DUP => 'op_dup',
        self::OP_NIP => 'op_nip',
        self::OP_OVER => 'op_over',
        self::OP_PICK => 'op_pick',
        self::OP_ROLL => 'op_roll',
        self::OP_ROT => 'op_rot',
        self::OP_SWAP => 'op_swap',
        self::OP_TUCK => 'op_tuck',

        self::OP_CAT => 'op_cat',
        self::OP_SUBSTR => 'op_substr',
        self::OP_LEFT => 'op_left',
        self::OP_RIGHT => 'op_right',
        self::OP_SIZE => 'op_size',

        self::OP_INVERT => 'op_invert',
        self::OP_AND => 'op_and',
        self::OP_OR => 'op_or',
        self::OP_XOR => 'op_xor',
        self::OP_EQUAL => 'op_equal',
        self::OP_EQUALVERIFY => 'op_equalverify',
        self::OP_RESERVED1 => 'op_reserved1',
        self::OP_RESERVED2 => 'op_reserved2',

        self::OP_1ADD => 'op_1add',
        self::OP_1SUB => 'op_1sub',
        self::OP_2MUL => 'op_2mul',
        self::OP_2DIV => 'op_2div',
        self::OP_NEGATE => 'op_negate',
        self::OP_ABS => 'op_abs',
        self::OP_NOT => 'op_not',
        self::OP_0NOTEQUAL => 'op_notequal',
        self::OP_ADD => 'op_add',
        self::OP_SUB => 'op_sub',
        self::OP_MUL => 'op_mul',
        self::OP_DIV => 'op_div',
        self::OP_MOD => 'op_mod',
        self::OP_LSHIFT => 'op_lshift',
        self::OP_RSHIFT => 'op_rshift',

        self::OP_BOOLAND => 'op_booland',
        self::OP_BOOLOR => 'op_boolor',
        self::OP_NUMEQUAL => 'op_numequal',
        self::OP_NUMEQUALVERIFY => 'op_numequalverify',
        self::OP_NUMNOTEQUAL => 'op_numnotequal',
        self::OP_LESSTHAN => 'op_lessthan',
        self::OP_GREATERTHAN => 'op_greaterthan',
        self::OP_LESSTHANOREQUAL => 'op_lessthanorequal',
        self::OP_GREATERTHANOREQUAL => 'op_greaterthanorequal',
        self::OP_MIN => 'op_min',
        self::OP_MAX => 'op_max',

        self::OP_WITHIN => 'op_within',

        self::OP_RIPEMD160 => 'op_ripemd160',
        self::OP_SHA1 => 'op_sha1',
        self::OP_SHA256 => 'op_sha256',
        self::OP_HASH160 => 'op_hash160',
        self::OP_HASH256 => 'op_hash256',
        self::OP_CODESEPARATOR => 'op_codeseparator',
        self::OP_CHECKSIG => 'op_checksig',
        self::OP_CHECKSIGVERIFY => 'op_checksigverify',
        self::OP_CHECKMULTISIG => 'op_checkmultisig',
        self::OP_CHECKMULTISIGVERIFY => 'op_checkmultisigverify',

        self::OP_NOP1 => 'op_nop1',
        self::OP_NOP2 => 'op_nop2',
        self::OP_NOP3 => 'op_nop3',
        self::OP_NOP4 => 'op_nop4',
        self::OP_NOP5 => 'op_nop5',
        self::OP_NOP6 => 'op_nop6',
        self::OP_NOP7 => 'op_nop7',
        self::OP_NOP8 => 'op_nop8',
        self::OP_NOP9 => 'op_nop9',
        self::OP_NOP10 => 'op_nop10',

        self::OP_INVALIDOPCODE => 'op_invalidopcode',
    );

    ///////////////////////////////////////////////////////
    // Opcodes
    function op_0(&$stack, &$bytecode, &$return) { /* Does nothing */ }

    function op_pushdata1(&$stack, &$bytecode, &$return) {
        // Read data length (1 byte)
        $size = unpack('C', substr($bytecode, 0, 1))[1];
        // Push data on the stack
        array_push($stack, substr($bytecode, 1, $size));

        // Skip the data we just pushed on stack
        $bytecode = substr($bytecode, $size+1);
    }
    function op_pushdata2(&$stack, &$bytecode, &$return) {
        // Read data length (2 bytes)
        $size = unpack('v', substr($bytecode, 0, 2))[1];
        // Push data on the stack
        array_push($stack, substr($bytecode, 2, $size));

        // Skip the data we just pushed on stack
        $bytecode = substr($bytecode, $size+2);
    }
    function op_pushdata4(&$stack, &$bytecode, &$return) {
        // Read data length (4 bytes)
        $size = unpack('V', substr($bytecode, 0, 4))[1];
        // Push data on the stack
        array_push($stack, substr($bytecode, 4, $size));

        // Skip the data we just pushed on stack
        $bytecode = substr($bytecode, $size+4);
    }

    function op_1negate(&$stack, &$bytecode, &$return) { array_push($stack, -1); }

    function op_1(&$stack, &$bytecode, &$return) { array_push($stack, 1); }
    function op_2(&$stack, &$bytecode, &$return) { array_push($stack, 2); }
    function op_3(&$stack, &$bytecode, &$return) { array_push($stack, 3); }
    function op_4(&$stack, &$bytecode, &$return) { array_push($stack, 4); }
    function op_5(&$stack, &$bytecode, &$return) { array_push($stack, 5); }
    function op_6(&$stack, &$bytecode, &$return) { array_push($stack, 6); }
    function op_7(&$stack, &$bytecode, &$return) { array_push($stack, 7); }
    function op_8(&$stack, &$bytecode, &$return) { array_push($stack, 8); }
    function op_9(&$stack, &$bytecode, &$return) { array_push($stack, 9); }
    function op_10(&$stack, &$bytecode, &$return) { array_push($stack, 10); }
    function op_11(&$stack, &$bytecode, &$return) { array_push($stack, 11); }
    function op_12(&$stack, &$bytecode, &$return) { array_push($stack, 12); }
    function op_13(&$stack, &$bytecode, &$return) { array_push($stack, 13); }
    function op_14(&$stack, &$bytecode, &$return) { array_push($stack, 14); }
    function op_15(&$stack, &$bytecode, &$return) { array_push($stack, 15); }
    function op_16(&$stack, &$bytecode, &$return) { array_push($stack, 16); }

    function op_nop(&$stack, &$bytecode, &$return) { /* Does nothing */ }
    function op_if(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_notif(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_verif(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_vernotif(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_else(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_endif(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_verify(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        $val = array_pop($stack);

        // Check
        $return = ($val === self::OP_TRUE);

        // False is not poped from the stack
        if (!$return) {
            array_push($stack, $val);
        }
    }
    function op_return(&$stack, &$bytecode, &$return) { $return = false; }

    function op_toaltstack(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_fromaltstack(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2drop(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2dup(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_3dup(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2over(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2rot(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2swap(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_ifdup(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_depth(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_drop(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_dup(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_nip(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_over(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_pick(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_roll(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_rot(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_swap(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_tuck(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_cat(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_substr(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_left(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_right(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_size(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_invert(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_and(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_or(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_xor(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_equal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_equalverify(&$stack, &$bytecode, &$return) {
        $this->op_equal($stack, $bytecode, $return);
        $this->op_verify($stack, $bytecode, $return);
    }
    function op_reserved1(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_reserved2(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_1add(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_1sub(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2mul(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_2div(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_negate(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_abs(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_not(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_notequal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_add(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_sub(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_mul(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_div(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_mod(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_lshift(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_rshift(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_booland(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_boolor(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_numequal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_numequalverify(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_numnotequal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_lessthan(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_greaterthan(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_lessthanorequal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_greaterthanorequal(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_min(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_max(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_within(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }

    function op_ripemd160(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        // Get value from the stack
        $val = array_pop($stack);

        // Hash value (ripemd160)
        if (function_exists('hash')) {
            $val = hash('ripemd160', $val, true);
        } else if (function_exists('mhash')) {
            $val = mhash(MHASH_RIPEMD160, $val);
        } else {
            throw new \Exception("Not implemented");
        }

        // Push hash on stack
        array_push($stack, $val);
    }
    function op_sha1(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        // Get value from the stack
        $val = array_pop($stack);

        // Hash value (sha1)
        if (function_exists('hash')) {
            $val = hash('sha1', $val, true);
        } else if (function_exists('mhash')) {
            $val = mhash(MHASH_SHA1, $val);
        } else {
            throw new \Exception("Not implemented");
        }

        // Push hash on stack
        array_push($stack, $val);
    }
    function op_sha256(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        // Get value from the stack
        $val = array_pop($stack);

        // Hash value (sha256)
        if (function_exists('hash')) {
            $val = hash('sha256', $val, true);
        } else if (function_exists('mhash')) {
            $val = mhash(MHASH_SHA256, $val);
        } else {
            throw new \Exception("Not implemented");
        }

        // Push hash on stack
        array_push($stack, $val);
    }
    function op_hash160(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        // Get value from the stack
        $val = array_pop($stack);

        // Hash value (sha256, then ripemd160)
        if (function_exists('hash')) {
            $val = hash('ripemd160', hash('sha256', $val, true), true);
        } else if (function_exists('mhash')) {
            $val = mhash(MHASH_RIPEMD160, mhash(MHASH_SHA256, $val));
        } else {
            throw new \Exception("Not implemented");
        }

        // Push hash on stack
        array_push($stack, $val);
    }
    function op_hash256(&$stack, &$bytecode, &$return) {
        // We need to pop a value from the stack
        if (count($stack) == 0) {
            throw new CScriptNotValid();
        }

        // Get value from the stack
        $val = array_pop($stack);

        // Hash value (sha256, then sha256)
        if (function_exists('hash')) {
            $val = hash('sha256', hash('sha256', $val, true), true);
        } else if (function_exists('mhash')) {
            $val = mhash(MHASH_SHA256, mhash(MHASH_SHA256, $val));
        } else {
            throw new \Exception("Not implemented");
        }

        // Push hash on stack
        array_push($stack, $val);
    }
    function op_codeseparator(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_checksig(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_checksigverify(&$stack, &$bytecode, &$return) {
        $this->op_checksig($stack, $bytecode, $return);
        $this->op_verify($stack, $bytecode, $return);
    }
    function op_checkmultisig(&$stack, &$bytecode, &$return) { throw new \Exception("Not implemented"); }
    function op_checkmultisigverify(&$stack, &$bytecode, &$return) {
        $this->op_checkmultisigverify($stack, $bytecode, $return);
        $this->op_verify($stack, $bytecode, $return);
    }

    function op_nop1(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop2(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop3(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop4(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop5(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop6(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop7(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop8(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop9(&$stack, &$bytecode, &$return) { /* Ignored */ }
    function op_nop10(&$stack, &$bytecode, &$return) { /* Ignored */ }

    function op_invalidopcode(&$stack, &$bytecode, &$return) { throw new CScriptNotValid(); }


    ///////////////////////////////////////////////////////
    // Script parsing
    /**
     * Run a raw script with a specified stack
     * @param array $stack
     * @param string $raw_script
     * @return bool
     */
    protected function runRawScript(&$stack, $raw_script) {
        $return = null;

        return false;
    }

    public function addElement($elem) {
        if (is_int($elem)) {
            if ($elem == -1 || ($elem >= 1 && $elem <= 16) ) {
                // OP Code
                $this->raw_data .= chr($elem + self::OP_1 - 1);
            } else {
                // BigNum
                $tmp = new BigNum\BigNumGMP();
                $tmp->fromInt($elem);
                $elem = $tmp;
            }
        }

        // Serialize the BigNumber
        if ($elem instanceof BigNum) {
            $network_serializer = new NetworkSerializer();
            $stream = fopen('php://memory', 'r+');

            // Serialize the BigNum
            $network_serializer->write_object($stream, $elem);
            fseek($stream, 0);
            $this->raw_data .= stream_get_contents($stream);

            fclose($stream);
        }
    }

    /**
     * Run the specified script
     * @param CScript $input The signature of the input transaction
     * @return bool The script return value
     */
    public function run($input) {
        $stack = array();

        // Load data
        $this->runRawScript($stack, $input->raw_data);
        // Run script
        return $this->runRawScript($stack, $this->raw_data);
    }
} 