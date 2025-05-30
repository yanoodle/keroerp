<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import WarningButton from "@/Components/WarningButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import { nextTick, ref, computed } from "vue";

const nameInput = ref(null);
const modal = ref(false);
const title = ref("");
const operation = ref(1);
const id = ref("");

const props = defineProps({
    inventories: { type: Object },
    refundItems: { type: Array },
});
const form = useForm({
    name: "",
    description: "",
    base_qty: "",
    in_demand_qty: "",
    vendor_name: "",
    vendor_contact: "",
    sell_price: "",
    buy_price: "",
});
const formPage = useForm({});
const onPageClick = (event) => {
    formPage.get(route("inventories.index", { page: event }));
};
const openModal = (
    op,
    name,
    description,
    base_qty,
    in_demand_qty,
    price,
    inventory
) => {
    modal.value = true;
    nextTick(() => nameInput.value.focus());
    operation.value = op;
    id.value = inventory;
    if (op == 1) {
        title.value = "Add Product";
    } else {
        title.value = "Edit Product";
        form.name = name;
        form.description = description;
        form.base_qty = base_qty;
        form.in_demand_qty = in_demand_qty;
        form.vendor_name = vendor_name;
        form.vendor_contact = vendor_contact;
        form.sell_price = sell_price;
        form.buy_price = buy_price;
    }
};
const closeModal = () => {
    modal.value = false;
    form.reset();
};
const save = () => {
    if (operation.value == 1) {
        form.post(route("inventories.store"), {
            onSuccess: () => {
                ok("Product Added");
            },
        });
    } else {
        form.put(route("inventories.update", id.value), {
            onSuccess: () => {
                ok("Product updated");
            },
        });
    }
};
const ok = (msj) => {
    form.reset();
    closeModal();
    Swal.fire({ title: msj, icon: "success" });
};
const deleteInventory = (inventory) => {
    const alerta = Swal.mixin({
        buttonStyling: true,
    });
    alerta
        .fire({
            title: "This action will delete " + inventory.name,
            icon: "exclamation",
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        })
        .then((result) => {
            if (result.isConfirmed) {
                form.delete(route("inventories.destroy", inventory), {
                    onSuccess: () => {
                        ok("Product deleted");
                    },
                });
            }
        });
};

const formatToIDR = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const updateStatus = (item) => {
    Swal.fire({
        title: `Add refund item to inventory?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({});
            form.put(route("refund-items.add", item.id), {
                onSuccess: () => {
                    item.status = "Added";
                    Swal.fire({
                        title: "Item added successfully!",
                        icon: "success",
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: "Error updating item.",
                        icon: "error",
                    });
                },
            });
        }
    });
};

const deleteRefundItem = (item) => {
    Swal.fire({
        title: `Are you sure you want to delete refund item "${item.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(
                route("inventories.refund-items.destroy", item.id),
                {
                    onSuccess: () => {
                        Swal.fire(
                            "Deleted!",
                            "Refund item has been deleted.",
                            "success"
                        );
                        const index = props.refundItems.findIndex(
                            (i) => i.id === item.id
                        );
                        if (index !== -1) props.refundItems.splice(index, 1);
                    },
                    onError: () => {
                        Swal.fire(
                            "Error!",
                            "Failed to delete refund item.",
                            "error"
                        );
                    },
                }
            );
        }
    });
};

const searchQuery = ref("");
const filterOption = ref("");

const filteredInven = computed(() => {
    let filtered = props.inventories.filter((inventory) => {
        const matchesSearch =
            inventory.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            inventory.description
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            new Date(inventory.created_at)
                .toLocaleDateString()
                .includes(searchQuery.value);

        return matchesSearch;
    });

    if (filterOption.value === "lowest_base") {
        const minBase = Math.min(...filtered.map((i) => Number(i.base_qty)));
        return filtered.filter((i) => Number(i.base_qty) === minBase);
    }
    if (filterOption.value === "highest_base") {
        const maxBase = Math.max(...filtered.map((i) => Number(i.base_qty)));
        return filtered.filter((i) => Number(i.base_qty) === maxBase);
    }
    if (filterOption.value === "lowest_demand") {
        const minDemand = Math.min(
            ...filtered.map((i) => Number(i.in_demand_qty))
        );
        return filtered.filter((i) => Number(i.in_demand_qty) === minDemand);
    }
    if (filterOption.value === "highest_demand") {
        const maxDemand = Math.max(
            ...filtered.map((i) => Number(i.in_demand_qty))
        );
        return filtered.filter((i) => Number(i.in_demand_qty) === maxDemand);
    }

    if (filterOption.value === "sort_base_asc") {
        return [...filtered].sort(
            (a, b) => Number(a.base_qty) - Number(b.base_qty)
        );
    }
    if (filterOption.value === "sort_base_desc") {
        return [...filtered].sort(
            (a, b) => Number(b.base_qty) - Number(a.base_qty)
        );
    }
    if (filterOption.value === "sort_demand_asc") {
        return [...filtered].sort(
            (a, b) => Number(a.in_demand_qty) - Number(b.in_demand_qty)
        );
    }
    if (filterOption.value === "sort_demand_desc") {
        return [...filtered].sort(
            (a, b) => Number(b.in_demand_qty) - Number(a.in_demand_qty)
        );
    }

    return filtered;
});
</script>

<template>
    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Inventory Module
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div
                        v-if="$page.props.auth.user.role === 'Admin'"
                        class="p-6 text-gray-900"
                    >
                        <PrimaryButton @click="openModal(1)">
                            Add
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <div
                class="m-6 flex gap-4 items-center bg-white p-4 rounded-lg shadow-md max-w-xl mx-auto"
            >
                <input
                    type="text"
                    placeholder="Search by name, description, or date..."
                    v-model="searchQuery"
                    class="flex-grow min-w-[200px] border border-gray-300 rounded-lg px-4 py-2 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                />

                <select
                    v-model="filterOption"
                    class="w-48 border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
                    <option value="">All Items</option>
                    <option value="lowest_base">Lowest Base Qty</option>
                    <option value="highest_base">Highest Base Qty</option>
                    <option value="lowest_demand">Lowest In Demand Qty</option>
                    <option value="highest_demand">
                        Highest In Demand Qty
                    </option>
                    <option value="sort_base_asc">
                        Sort Base Qty Ascending
                    </option>
                    <option value="sort_base_desc">
                        Sort Base Qty Descending
                    </option>
                    <option value="sort_demand_asc">
                        Sort In Demand Qty Ascending
                    </option>
                    <option value="sort_demand_desc">
                        Sort In Demand Qty Descending
                    </option>
                </select>
            </div>

            <div class="mt-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto my-4">
                    <table
                        class="w-full text-sm text-left rtl:text-right text-gray-500"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3">Base Qty</th>
                                <th scope="col" class="px-6 py-3">
                                    In Demand Qty
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Sell Price
                                </th>
                                <th scope="col" class="px-6 py-3">Buy Price</th>
                                <th scope="col" class="px-6 py-3"></th>
                                <th scope="col" class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inv, i) in filteredInven"
                                :key="inv.id"
                                class="bg-white border-b"
                            >
                                <td class="px-6 py-4">{{ i + 1 }}</td>
                                <th
                                    scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                >
                                    {{ inv.name }}
                                </th>
                                <td class="px-6 py-4">{{ inv.description }}</td>
                                <td class="px-6 py-4">{{ inv.base_qty }}</td>
                                <td class="px-6 py-4">
                                    {{ inv.in_demand_qty }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatToIDR(inv.sell_price) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatToIDR(inv.buy_price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <WarningButton
                                        v-if="
                                            $page.props.auth.user.role ===
                                            'Admin'
                                        "
                                        @click="
                                            ($event) =>
                                                openModal(
                                                    2,
                                                    inv.name,
                                                    inv.description,
                                                    inv.base_qty,
                                                    inv.in_demand_qty,
                                                    inv.price,
                                                    inv.id
                                                )
                                        "
                                    >
                                        Edit
                                    </WarningButton>
                                </td>
                                <td class="px-6 py-4">
                                    <DangerButton
                                        v-if="
                                            $page.props.auth.user.role ===
                                            'Admin'
                                        "
                                        @click="
                                            ($event) => deleteInventory(inv)
                                        "
                                        type="button"
                                        class="text-white bg-red-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2"
                                    >
                                        Delete
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Refund Items
                </h3>
                <div class="relative overflow-x-auto my-4">
                    <table
                        class="w-full text-sm text-left rtl:text-right text-gray-500"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3">Quantity</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3"></th>
                                <th scope="col" class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in refundItems"
                                :key="item.id"
                                class="bg-white border-b"
                            >
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    {{
                                        new Date(
                                            item.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                >
                                    {{ item.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ item.description }}
                                </td>
                                <td class="px-6 py-4">{{ item.qty }}</td>
                                <td class="px-6 py-4">{{ item.status }}</td>
                                <td class="px-6 py-4">
                                    <template v-if="item.status === 'Pending'">
                                        <PrimaryButton
                                            @click="() => updateStatus(item)"
                                            >Add</PrimaryButton
                                        >
                                    </template>
                                </td>
                                <td class="px-6 py-4">
                                    <DangerButton
                                        v-if="
                                            $page.props.auth.user.role ===
                                                'Admin' &&
                                            item.status === 'Pending'
                                        "
                                        @click="() => deleteRefundItem(item)"
                                        class="ml-2"
                                    >
                                        Reject
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="modal" @close="closeModal">
            <h2 class="p-3 text-lg font.medium text-gray-900">{{ title }}</h2>
            <div class="p-3 mt-6">
                <InputLabel for="name" value="Name"></InputLabel>
                <TextInput
                    id="name"
                    ref="nameInput"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Name"
                ></TextInput>
                <InputError
                    :message="form.errors.name"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="description" value="Description"></InputLabel>
                <TextInput
                    id="description"
                    ref="nameInput"
                    v-model="form.description"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Description"
                ></TextInput>
                <InputError
                    :message="form.errors.description"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="base_qty" value="Base Qty"></InputLabel>
                <TextInput
                    id="base_qty"
                    ref="nameInput"
                    v-model="form.base_qty"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Base Qty"
                ></TextInput>
                <InputError
                    :message="form.errors.base_qty"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel
                    for="in_demand_qty"
                    value="In Demand Qty"
                ></InputLabel>
                <TextInput
                    id="in_demand_qty"
                    ref="nameInput"
                    v-model="form.in_demand_qty"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="In Demand Qty"
                ></TextInput>
                <InputError
                    :message="form.errors.in_demand_qty"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="vendor_name" value="Vendor Name"></InputLabel>
                <TextInput
                    id="vendor_name"
                    ref="nameInput"
                    v-model="form.vendor_name"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Vendor Name"
                ></TextInput>
                <InputError
                    :message="form.errors.vendor_name"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel
                    for="vendor_contact"
                    value="Vendor Contact"
                ></InputLabel>
                <TextInput
                    id="vendor_contact"
                    ref="nameInput"
                    v-model="form.vendor_contact"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Vendor Contact"
                ></TextInput>
                <InputError
                    :message="form.errors.vendor_contact"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="sell_price" value="Sell Price"></InputLabel>
                <TextInput
                    id="sell_price"
                    ref="nameInput"
                    v-model="form.sell_price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Sell Price"
                ></TextInput>
                <InputError
                    :message="form.errors.sell_price"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="buy_price" value="Buy Price"></InputLabel>
                <TextInput
                    id="buy_price"
                    ref="nameInput"
                    v-model="form.buy_price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Buy Price"
                ></TextInput>
                <InputError
                    :message="form.errors.buy_price"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="form.processing" @click="save"
                    >Save</PrimaryButton
                >
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
