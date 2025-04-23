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
import { nextTick, ref } from "vue";

const nameInput = ref(null);
const modal = ref(false);
const title = ref("");
const operation = ref(1);
const id = ref("");

const props = defineProps({
    inventories: { type: Object },
});
const form = useForm({
    name: "",
    description: "",
    base_qty: "",
    in_demand_qty: "",
    price: "",
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
        form.price = price;
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
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Inventory Module
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Welcome, You're logged in as Inventory
                    </div>
                    <div class="p-6 text-gray-900">
                        <PrimaryButton @click="($event) => openModal(1)">
                            Add
                        </PrimaryButton>
                    </div>
                </div>
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
                                <th scope="col" class="px-6 py-3">Price</th>
                                <th scope="col" class="px-6 py-3"></th>
                                <th scope="col" class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inv, i) in inventories"
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
                                <td class="px-6 py-4">{{ inv.price }}</td>
                                <td class="px-6 py-4">
                                    <WarningButton
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
                    placeholder="base_qty"
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
                <InputLabel for="price" value="Price"></InputLabel>
                <TextInput
                    id="price"
                    ref="nameInput"
                    v-model="form.price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Price"
                ></TextInput>
                <InputError
                    :message="form.errors.price"
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
