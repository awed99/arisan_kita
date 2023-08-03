import { useState, useEffect, useRef } from 'react';
import { Toastr, ToastrRef, ToastrProps } from '@paljs/ui/Toastr';
import { useRouter } from 'next/router'
import { Button } from '@paljs/ui/Button';
import { InputGroup } from '@paljs/ui/Input';
import { Checkbox } from '@paljs/ui/Checkbox';
import React from 'react';
import Link from 'next/link';
import Spinner from '@paljs/ui/Spinner';
import Progress from '@paljs/ui/ProgressBar';

import Auth, { Group } from 'components/Auth';
import Socials from 'components/Auth/Socials';
import Layout from 'Layouts';

import { validateEmail, checkPassword } from 'src/helpers/hooks';
import { size } from 'lodash';
import CryptoJS from "crypto-js";
import Store from 'store';

export default function RequestPassword() {
  const toastrRef = useRef<ToastrRef>(null);
  const [values, setValues] = useState('')
  const [loading, setLoading] = useState(false)
  const [isError, setIsError] = useState(false)

  useEffect(() => {
    let _isError = 0
    if (!validateEmail(values)) {
      _isError++
    }

    if (_isError > 0) {
      setIsError(true)
    } else {
      setIsError(false)
    }
  }, [values])

  const handleSubmit = (event:any) => {
    event.preventDefault()
    
    setLoading(true)
    const _data = new FormData()
    _data.append('email', values.trim().toLocaleLowerCase())
    _data.append('action', 'cp')
    fetch(`${process.env.NEXT_PUBLIC_BE_API}user/validation_no_token`,
    {
      method: 'POST',
      body: _data
    })
    .then((res) => res.json())
    .then((res) => {
      if (res?.status === '000') { 
        toastrRef.current?.add(res?.message, 'Success', {
          position: 'topEnd',
          status: 'Success',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
        setValues('')
        setTimeout(() => window.location.href='/auth/login', 3000)
      } else {
        toastrRef.current?.add(res?.message, 'Danger', {
          position: 'topEnd',
          status: 'Danger',
          duration: 3000,
          hasIcon: true,
          destroyByClick: true,
          preventDuplicates: false,
        })
      }

      setTimeout(() => setLoading(false), 3000)
    })
    .catch(() => setLoading(false))
  }

  useEffect(() => {
    if (
      Store.get('auth-user') &&
      CryptoJS.AES.decrypt(`${Store.get('auth-user')}`, `${process.env.NEXT_PUBLIC_SECRET_KEY}`).toString(CryptoJS.enc.Utf8)
    ) {
      window.location.href='/'
    }

  }, [])

  return (
    <Layout title="Forgot Password">
      <Auth title="Forgot Password" subTitle="Enter your email address and we’ll send a link to reset your password">
        <form onSubmit={e => handleSubmit(e)}>
          <InputGroup fullWidth status={!validateEmail(values) ? 'Warning' : 'Success'}>
            <input type="email" onChange={(e) => setValues(e?.target?.value)} placeholder="Email Address" />
          </InputGroup>
          <Button status="Success" type="submit" shape="SemiRound" fullWidth disabled={isError || loading}>
            Request Password
          </Button>
        </form>
        <Group>
          <Link href="/auth/login">
            <a>Back to Log In</a>
          </Link>
          <Link href="/auth/register">
            <a>Register</a>
          </Link>
        </Group>
      </Auth>
      <Toastr ref={toastrRef} />
      {loading && <Spinner size="Large" status="Danger" />}
    </Layout>
  );
}
