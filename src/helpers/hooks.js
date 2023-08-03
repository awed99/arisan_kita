import { omit } from 'lodash'

export const formatRupiah = (angka, prefix) => {
  if (angka) {
    const number_string = angka?.replace(/[^,\d]/g, '').toString()
    const splitx   		= number_string?.split('.')
    const sisa     		= splitx[0]?.length % 3
    let rupiah     		= splitx[0]?.substr(0, sisa)
    const ribuan     		= splitx[0]?.substr(sisa).match(/\d{3}/gi)

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        const separator = sisa ? ',' : ''
        rupiah += separator + ribuan.join(',')
    }

    rupiah = splitx[1] != undefined ? rupiah + '.' + splitx[1] : rupiah

    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '')
  } else {
    return angka
  }
}

const insertItem = (item, datas) => {
    item.id = generateId(datas)
    item.inEdit = false
    datas.unshift(item)

    return datas
}

export const addNew = (datas, setDatas) => {
    const newDataItem = {
      inEdit: true,
      Discontinued: false,
    };
    setDatas([newDataItem, ...datas]);
}

export const add = (dataItem, datas, setDatas) => {
    dataItem.inEdit = true
    const newData = insertItem(dataItem, datas)
    setDatas(newData)
}

export const enterEdit = (dataItem, datas, setDatas) => {
    setDatas(
        datas.map((item) =>
        item.id === dataItem.id
          ? {
              ...item,
              inEdit: true,
            }
          : item
      )
    )
}

export const remove = (dataItem, datas, setDatas, removeFunc) => {
    removeFunc(dataItem)
    cancel(dataItem, datas, setDatas)
}

export const update = (dataItem, datas, setDatas, updateFunc) => {
    updateFunc(dataItem)
    cancel(dataItem, datas, setDatas)
}

export const discard = (dataItem, datas, setDatas) => {
    const newData = [...datas]
    newData.splice(0, 1)
    setDatas(newData)
}

export const cancel = (dataItem, datas, setDatas) => {
  const newData = datas.map((item) =>
    item.id === dataItem.id ? omit(item, ['inEdit']) : item
  )
  setDatas(newData)
}

export const itemChange = (event, datas, setDatas) => {
    const newData = datas.map((item) =>
      item.id === event.dataItem.id
        ? {
            ...item,
            [event.field || ""]: event.value,
          }
        : item
    );
    setDatas(newData);
}

export const MyCommandCell = props => {
    const { dataItem } = props
    const inEdit = dataItem[props.editField]
    const isNewItem = dataItem.id === undefined

    return inEdit ? (
        <td className="k-command-cell">
        <button
            className="k-button k-grid-save-command"
            onClick={() => (isNewItem ? props?.add(dataItem) : props?.update(dataItem))}
        >
            {isNewItem ? "Add" : "Update"}
        </button>
        <button
            className="k-button k-grid-cancel-command"
            onClick={() => (isNewItem ? props?.discard(dataItem) : props?.cancel(dataItem))}
        >
            {isNewItem ? "Discard" : "Cancel"}
        </button>
        </td>
    ) : (
        <td className="k-command-cell">
        {(props?.is_edit === true || props?.is_edit === undefined) && <button
            className="k-primary k-button k-grid-edit-command"
            onClick={() => props?.edit(dataItem)}
        >
            Edit
        </button>}
        {(props?.is_remove === true || props?.is_remove === undefined) && <button
            className="k-button k-grid-remove-command"
            onClick={() =>
            confirm("Confirm deleting: " + dataItem.ProductName) &&
            props?.remove(dataItem)
            }
        >
            Remove
        </button>}
        </td>
    )
}

const escapeRegExp = (string) => {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}

export const replaceAll = (str, find, replace) => {
    return str.replace(new window.RegExp(escapeRegExp(find), 'g'), replace)

    // return str.replace(/:insertx:/g, 'hello!')
}

export const validateEmail = (email) => {
  return email?.toString()?.match(
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
  );
}

export const checkPassword = (password) => {
  let strength = 0;
  const res = {error: '', valid:''}
  if (password.match(/[a-z]+/)) {
    strength += 1;
  }
  if (password.match(/[A-Z]+/)) {
    strength += 1;
  }
  if (password.match(/[0-9]+/)) {
    strength += 1;
  }
  if (password.match(/[$@#&!]+/)) {
    strength += 1;
  }

  if (password.length < 6) {
    res['error'] = "minimum number of characters is 6";
  }

  if (password.length > 20) {
    res['error'] = "maximum number of characters is 20";
  }

  switch (strength) {
    case 0:
      res['error'] = 'Weak!';
      break;

    case 1:
      res['error'] = 'Weak!';
      break;

    case 2:
      res['error'] = 'Weak!';
      break;

    case 3:
      res['valid'] = 'Medium';
      break;

    case 4:
      res['valid'] = 'Strong';
      break;
  }

  // console.log('res: ', res);
  return res
}
